<?php

declare(strict_types=1);

namespace BoltRedirector;

use Bolt\Widget\Injector\RequestZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RedirectSubscriber implements EventSubscriberInterface
{
    /** @var Redirector */
    private $redirector;

    public function __construct(Redirector $redirector)
    {
        $this->redirector = $redirector;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (RequestZone::isForBackend($request) || RequestZone::isForAsync($request)) {
            return;
        }

        $locations = [
            rtrim($request->getRequestUri(), '/'),
            rtrim($request->getUri(), '/'),
            rtrim($request->getPathInfo(), '/'),
            urldecode(rtrim($request->getUri(), '/')),
            urldecode(rtrim($request->getPathInfo(), '/')),
        ];


        $redirect = $this->redirector->findFor($locations);

        if ($redirect) {
            if ($this->redirector->getStatusCode() >= 400 && $this->redirector->getStatusCode() < 500) {
                $event->setResponse(new Response('', $this->redirector->getStatusCode()));
            } else {
                $event->setResponse(new RedirectResponse($redirect, $this->redirector->getStatusCode()));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        // todo: If we can get the config from Bolt in the request,
        // let's listen to that instead, because it's faster.
        return [
            KernelEvents::RESPONSE => [['onKernelResponse', 0]]
        ];
    }
}

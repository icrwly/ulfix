<?php

namespace Drupal\ul_samlauth\EventSubscriber;

use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects logged-in users when access is denied to /saml/login.
 *
 * @see \Drupal\samlauth\EventSubscriber\AccessDeniedSubscriber.
 */
class AccessDeniedSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new redirect subscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * Redirects users when access is denied.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The event to process.
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();
    if ($exception instanceof AccessDeniedHttpException && $this->account->isAuthenticated()) {
      $route_name = RouteMatch::createFromRequest($event->getRequest())->getRouteName();
      switch ($route_name) {
        case 'samlauth.saml_controller_login':
        case 'samlauth.saml_controller_acs':
          // Redirect an authenticated user to the home page.
          $url = Url::fromRoute('<front>')->toString();
          $event->setResponse(new LocalRedirectResponse($url));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Use a higher priority than the samlauth module event subscriber
    // because we don't want to hit that subscriber in this case.
    $events[KernelEvents::EXCEPTION][] = ['onException', 80];
    return $events;
  }

}

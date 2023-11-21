<?php

namespace Drupal\ul_samlauth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Samlauth Controller.
 */
class ULSamlauth extends ControllerBase implements ContainerInjectionInterface {

  /**
   * ConfigFactory object.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;
  }

  /**
   * Redirects the user login.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns the redirect response object.
   */
  public function redirectUserLogin() {

    $url = Url::fromRoute('samlauth.saml_controller_login', [], [
      'query' => ['return' => '/'],
      'absolute' => TRUE,
    ]);
    return new RedirectResponse($url->toString());
  }

  /**
   * Redirects the user logout.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns the redirect response object.
   */
  public function redirectUserLogout() {

    $request = \Drupal::request();
    $request->getSession()->clear();
    $url = Url::fromRoute('samlauth.saml_controller_logout', [], [
      'query' => ['return' => '/'],
      'absolute' => TRUE,
    ]);
    return new RedirectResponse($url->toString());
  }

  /**
   * Redirects the user registration.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns the redirect response object.
   */
  public function redirectUserRegistration() {

    $url = $this->config('ul_samlauth.settings')->get('registration_url');
    return new TrustedRedirectResponse($url);
  }

  /**
   * Redirects the user account page.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns the redirect response object.
   */
  public function redirectUserPage() {

    $url = $this->config('ul_samlauth.settings')->get('account_url');
    return new TrustedRedirectResponse($url);
  }

}

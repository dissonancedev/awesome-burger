<?php

namespace Drupal\product_pages\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ContactForm extends FormBase {
  /**
   * @inheritdoc
   */
  public function getFormId() {
    return 'product_pages_contact_form';
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h1',
      '#value' => $this->t('HEADLINE - LOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR.'),
    ];

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('LOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR. LOREM IPSUM DOLLAR SINAR. OREM. SIN LOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR. LOREM IPSUM DOLLAR
SINAR. OREM. SINLOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR. LOREM IPSUM DOLLAR SINAR. OREM. SIN LOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR.
LOREM IPSUM DOLLAR SINAR. OREM. SIN LOREM IPSUM DOLLAR SINAR. OREM. SIN AR DOLLAR. LOREM IPSUM DOLLAR SINAR. OREM. SIN LOREM IPSUM DOLLAR SINAR.
OREM. SIN AR DOLLAR. LOREM IPSUM DOLLAR SINAR. OREM. SIN'),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t('Name'),
      ],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#attributes' => [
        'placeholder' => $this->t('Email'),
      ],
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#attributes' => [
        'placeholder' => $this->t('Message'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Thank you for contacting us. We will get to you ASAP.'));
  }
}

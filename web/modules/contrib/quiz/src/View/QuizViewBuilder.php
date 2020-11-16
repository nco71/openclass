<?php

namespace Drupal\quiz\View;

use Drupal;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Link;
use Drupal\quiz\Entity\Quiz;
use Drupal\quiz\Util\QuizUtil;
use function _quiz_format_duration;
use function _quiz_get_quiz_name;

class QuizViewBuilder extends EntityViewBuilder {

  public function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    /* @var $entity Quiz */

    $stats = [
      [
        ['header' => TRUE, 'width' => '25%', 'data' => t('Questions')],
        $entity->getNumberOfQuestions(),
      ],
    ];

    if ($entity->get('show_attempt_stats')->getString()) {
      $takes = $entity->get('takes')->getString() == 0 ? t('Unlimited') : $entity->get('takes')->getString();
      $stats[] = [
        ['header' => TRUE, 'data' => t('Attempts allowed')],
        $takes,
      ];
    }

    if ($entity->get('quiz_date')->isEmpty()) {
      $stats[] = [
        ['header' => TRUE, 'data' => t('Available')],
        t('Always'),
      ];
    }
    else {
      $stats[] = [
        ['header' => TRUE, 'data' => t('Opens')],
        $entity->get('quiz_date')->value,
      ];
      $stats[] = [
        ['header' => TRUE, 'data' => t('Closes')],
        $entity->get('quiz_date')->end_value,
      ];
    }

    if (!$entity->get('pass_rate')->isEmpty()) {
      $stats[] = [
        ['header' => TRUE, 'data' => t('Pass rate')],
        $entity->get('pass_rate')->getString() . ' %',
      ];
    }

    if (!$entity->get('time_limit')->isEmpty()) {
      $stats[] = [
        ['header' => TRUE, 'data' => t('Time limit')],
        _quiz_format_duration($entity->get('time_limit')->getString()),
      ];
    }

    $stats[] = [
      ['header' => TRUE, 'data' => t('Backwards navigation')],
      $entity->get('backwards_navigation') ? t('Allowed') : t('Forbidden'),
    ];

    if ($display->getComponent('stats')) {
      $build['stats'] = [
        '#id' => 'quiz-view-table',
        '#theme' => 'table',
        '#rows' => $stats,
        '#weight' => -1,
      ];
    }

    $available = $entity->access('take', NULL, TRUE);
    // Check the permission before displaying start button.
    if (!$available->isForbidden()) {
      if (is_a($available, AccessResultReasonInterface::class)) {
        // There's a friendly success message available. Only display if we are
        // viewing the quiz.
        // @todo does not work because we cannot pass allowed reason, only
        // forbidden reason. The message is displayed in quiz_quiz_access().
        if (\Drupal::routeMatch() == 'entity.quiz.canonical') {
          Drupal::messenger()->addMessage($available->getReason());
        }
      }
      // Add a link to the take tab.
      $link = Link::createFromRoute(t('Start @quiz', ['@quiz' => QuizUtil::getQuizName()]), 'entity.quiz.take', ['quiz' => $entity->id()], [
        'attributes' => [
          'class' => [
            'quiz-start-link',
            'button',
          ],
        ],
      ]);
      $build['take'] = [
        '#cache' => ['max-age' => 0],
        '#markup' => $link->toString(),
        '#weight' => 2,
      ];
    }
    else {
      $build['take'] = [
        '#cache' => ['max-age' => 0],
        '#markup' => '<div class="quiz-not-available">' . $available->getReason() . '</div>',
        '#weight' => 2,
      ];
    }
  }

}

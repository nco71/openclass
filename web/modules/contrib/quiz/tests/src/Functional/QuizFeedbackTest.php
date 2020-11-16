<?php

namespace Drupal\Tests\quiz\Functional;

use Drupal\quiz\Entity\QuizFeedbackType;

/**
 * Test quiz feedback.
 *
 * @group Quiz
 */
class QuizFeedbackTest extends QuizTestBase {

  public static $modules = ['quiz_truefalse'];

  /**
   * Test question feedback.
   *
   * Note that we are only testing if any feedback displays, each question type
   * has its own tests for testing feedback returned from that question type.
   */
  public function testAnswerFeedback() {
    $this->drupalLogin($this->admin);
    $quiz = $this->createQuiz();

    // 2 questions.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz);

    $review_options = [
      'attempt' => t('Your answer'),
      'correct' => t('Correct?'),
      'score' => t('Score'),
      'answer_feedback' => t('Feedback'),
      'solution' => t('Correct answer'),
    ];

    $this->drupalLogin($this->user);

    // Answer the first question.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));

    // Check feedback after the Question.
    foreach ($review_options as $option => $text) {
      $quiz->review_options = ['question' => [$option => $option]];
      $quiz->save();

      $this->drupalGet("quiz/{$quiz->id()}/take/1/feedback");
      $this->assertText('Question 1');
      $this->assertRaw('<th>' . $text . '</th>');
      foreach ($review_options as $option2 => $text2) {
        if ($option != $option2) {
          $this->assertNoRaw('<th>' . $text2 . '</th>');
        }
      }
    }

    // Feedback only after the quiz.
    $this->drupalGet("quiz/{$quiz->id()}/take/1/feedback");
    $this->clickLink(t('Next question'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Finish'));

    // Check feedback after the Quiz.
    foreach ($review_options as $option => $text) {
      $quiz->review_options = ['end' => [$option => $option]];
      $quiz->save();

      $this->drupalGet("quiz/{$quiz->id()}/result/1");
      $this->assertRaw('<th>' . $text . '</th>');
      foreach ($review_options as $option2 => $text2) {
        if ($option != $option2) {
          $this->assertNoRaw('<th>' . $text2 . '</th>');
        }
      }
    }
  }

  /**
   * Test general Quiz question feedback.
   */
  public function testQuestionFeedback() {
    $this->drupalLogin($this->admin);

    // Turn on question feedback at the end.
    $quiz = $this->createQuiz(
      [
        'review_options' => ['end' => ['question_feedback' => 'question_feedback']],
      ]
    );

    // Add 2 questions with general question feedback.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
      'feedback' => 'Feedback for TF test.',
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
      'feedback' => 'Feedback for TF test.',
    ]);
    $this->linkQuestionToQuiz($question2, $quiz);

    // Test.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
    $this->assertNoText('Feedback for TF test.');
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('Feedback for TF test.');
  }

  /**
   * Test no feedback.
   */
  public function testNoFeedback() {
    $this->drupalLogin($this->admin);

    // Turn off question feedback.
    $quiz = $this->createQuiz(
      [
        'review_options' => [],
      ]
    );

    // Add 2 questions with general question feedback.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
      'feedback' => 'Feedback for TF test.',
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
      'feedback' => 'Feedback for TF test.',
    ]);
    $this->linkQuestionToQuiz($question2, $quiz);

    // Test.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('You have finished this Quiz');
  }

  /**
   * Test Quiz question body feedback.
   */
  public function testQuestionBodyFeedback() {
    $this->drupalLogin($this->admin);

    // Asolutely no feedback.
    $quiz = $this->createQuiz(
      [
        'review_options' => [],
      ]
    );

    // Set up a Quiz with one question that has a body and a summary.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'body' => 'TF 1 body text',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);

    // Test no feedback.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertNoText('TF 1 body text');

    // Test full feedback.
    $quiz->review_options = ['end' => ['quiz_question_view_full' => 'quiz_question_view_full']];
    $quiz->save();
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('TF 1 body text');
  }

  /**
   * Test custom feedback types.
   */
  public function testFeedbackTimes() {
    $this->drupalLogin($this->admin);

    $component = [
      'expression' => [
        'id' => 'rules_and',
        'conditions' => [
          [
            'id' => 'rules_condition',
            'uuid' => 'ca2a6b2f-3b17-449e-b913-d64b52c17203',
            'weight' => 2,
            'context_values' => [
              'operation' => '==',
              'value' => '2',
            ],
            'context_mapping' => [
              'data' => 'quiz_result.attempt.value',
            ],
            'condition_id' => 'rules_data_comparison',
            'negate' => 0,
          ],
        ],
      ],
      'context_definitions' => [
        'quiz_result' => [
          'type' => 'entity:quiz_result',
          'label' => 'Quiz result',
          'description' => 'Quiz result to evaluate feedback',
        ],
      ],
    ];

    QuizFeedbackType::create([
        'label' => 'After two attempts',
        'id' => 'after2attempts',
        'component' => $component,
      ]
    )->save();

    // Feedback but, only after second attempt (rule).
    $quiz = $this->createQuiz(
      [
        'review_options' => ['after2attempts' => ['solution' => 'solution']],
      ]
    );

    // Set up a Quiz with one question that has a body and a summary.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);

    // Test no feedback.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertNoText('Correct answer');

    // Take again.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('Correct answer');
  }

}

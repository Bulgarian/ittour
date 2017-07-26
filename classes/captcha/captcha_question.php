<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

define('CAPTCHA_QUESTION_SESSION_TAG',  'A467E453-6C43-4E3F-AE68-B155AD56200F');

class captcha_question {
    
    var $vowels = array('a','e','i','o','u','y'); 
    var $consonants = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','z',);

    var $arr_numbers = array('first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth');

    var $words_amount;   // number of words in sentence
    var $max_words_lenth; // max lenth of words in sentence
    var $min_words_lenth; // min lenth of words in sentence
    
    // generated values
    var $answer = null;
    var $question = null;
    var $answer_word_number = null;


    function captcha_question ($words_amount = 10, $max_words_lenth = 8, $min_words_lenth = 3) {
        $this->words_amount = $words_amount;
        $this->max_words_lenth = $max_words_lenth;
        $this->min_words_lenth = $min_words_lenth;
        $this->render();
    }
    
    function render()  {
        if (!($this->answer = session(CAPTCHA_QUESTION_SESSION_TAG)))
            $this->reset();
    }
    
    function reset() {
        $this->generate_captcha_question();
        $_SESSION[CAPTCHA_QUESTION_SESSION_TAG] = $this->answer;
    }
    
    function generate_captcha_question () {
        
        $this->answer_word_number = rand(1, $this->words_amount);
        
        $sentence = array();
        for($i = 1; $i <= $this->words_amount; $i++) {
            $word = '';
            $word_lenth = rand($this->min_words_lenth, $this->max_words_lenth);
            for($j = 0; $j < $word_lenth; $j++) {
                $symbols = ($j%2 ? $this->vowels : $this->consonants);
                $index = rand(0, count($symbols) - 1);
                $word .= $symbols[$index];
            }
            $sentence []= $word;
        }
        $this->answer = $sentence[$this->answer_word_number-1];
        $this->question = implode(" ", $sentence);

    }
    
    function get_question_string () {
        
        $this->reset();
        $answer_word_number = ($this->answer_word_number > 10 ? $this->answer_word_number.'th' : $this->arr_numbers[$this->answer_word_number-1]);
        return 'What is the '.$answer_word_number.' word in the phrase "'.$this->question.'"?';
        
    }
    

}

//  function generate_captcha_question ($words_amount = 10) {
//        
//      $arr1 = array('a','e','i','o','u','y'); // 6
//      $arr2 = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','z',); // 20
//      $arr_numbers = array('first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth');

//      $answer_word_number = rand(1, $words_amount);
//      $result = array('answer_word_number' => ($answer_word_number > 10 ? $answer_word_number.'th' : $arr_numbers[$answer_word_number-1]));
//      $sentence = array();

//      for($i = 1; $i <= $words_amount; $i++) {
//          $word = '';
//          $word_lenth = rand(3, 8);
//          for($j = 0; $j < $word_lenth; $j++) {
//              $symbols = ($j%2 ? $arr1 : $arr2);
//              $index = rand(0, count($symbols) - 1);
//              $word .= $symbols[$index];
//          }
//          $sentence []= $word;
//      }
//      $result['answer'] = $sentence[$answer_word_number-1];
//      $result['question'] = implode(" ", $sentence);
//      return $result;

//  }

?>

<?php

class shopRevCount
{
    public static function RevCount($params) {
      $reviews_model = new shopProductReviewsModel();
      $count = $reviews_model->count($params);
      return $count;
     }
}
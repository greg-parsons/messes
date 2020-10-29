<?php

$config = array(
  'admin' => array(
    'core:AdminPassword',
  ),

  'example-userpass' => array(
    'exampleauth:UserPass',
    'samluser1:samluser1' => array(
      'uid' => array('1'),
      'email' => 'samluser1@example.com',
    ),
    'samluser2:samluser2' => array(
      'uid' => array('2'),
      'email' => 'samluser2@example.com',
    ),
    'samluser3:samluser3' => array(
      'uid' => array('3'),
      'email' => 'samluser3@example.com',
    ),
    'samluser4:samluser4' => array(
      'uid' => array('4'),
      'email' => 'samluser4@example.com',
    ),
  ),
);

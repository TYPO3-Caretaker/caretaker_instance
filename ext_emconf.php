<?php
$EM_CONF[$_EXTKEY] = array (
  'title' => 'Caretaker Instance',
  'description' => 'Client for caretaker observation system',
  'category' => 'misc',
  'author' => 'Martin Ficzel, Thomas Hempel, Christopher Hlubek, Tobias Liebig, Jan Haffner',
  'author_email' => 'ficzel@work.de,hempel@work.de,hlubek@networkteam.com,typo3@etobi.de',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'lockType' => '',
  'author_company' => '',
  'version' => '2.0.0',
  'constraints' => 
  array (
    'depends' =>
    array (
      'typo3' => '8.7.0-9.5.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  'autoload' =>
  array (
    'classmap' =>
    array (
      0 => 'services',
      1 => 'classes',
      2 => 'eid',
    ),
  ),
  '_md5_values_when_last_written' => '',
);

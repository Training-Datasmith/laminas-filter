<?php

declare(strict_types=1);

/**
 * Example: composing filter chains with laminas-filter.
 *
 * Run from the laminas-filter project root:
 *   php examples/filter_chain.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Filter\FilterChain;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StringToLower;
use Laminas\Filter\PregReplace;
use Laminas\Filter\StripTags;
use Laminas\Filter\HtmlEntities;

// --- Basic filter chain ---
$chain = new FilterChain();
$chain->attach(new StringTrim())
      ->attach(new StripTags())
      ->attach(new StringToLower());

$input = '  <b>Hello</b>, WORLD!  ';
echo "Input:    " . var_export($input, true) . "\n";
echo "Filtered: " . var_export($chain->filter($input), true) . "\n\n";

// --- PregReplace to slugify ---
$slugChain = new FilterChain();
$slugChain->attach(new StringTrim())
          ->attach(new StringToLower())
          ->attach(new PregReplace(['pattern' => '/[^a-z0-9]+/', 'replacement' => '-']))
          ->attach(new PregReplace(['pattern' => '/^-|-$/', 'replacement' => '']));

$title = '  My First Blog Post! (2026)  ';
echo "Title:  " . $title . "\n";
echo "Slug:   " . $slugChain->filter($title) . "\n\n";

// --- HtmlEntities to safely escape user content ---
$escaper = new HtmlEntities(['encoding' => 'UTF-8']);
$userInput = '<script>alert("XSS")</script> & "quotes"';
echo "Raw:     " . $userInput . "\n";
echo "Escaped: " . $escaper->filter($userInput) . "\n";

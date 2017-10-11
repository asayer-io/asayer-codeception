<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check the product page');

$I->amOnPage("/");

$I->click(['css' => 'a[href="product.html"]:not([class])']);

$I->seeInTitle('QA-as-a-Service | Asayer');
<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
// this class is used for Asayer-reporting

class AsayerReporting extends \Codeception\Module
{
    protected $apikey;
    protected $sessionId;
    protected $stepsStatus = array();
    protected $stepPrefix;
    protected $requirementID;
    protected $sessionStatus = true;

    // HOOK: before each suite
    public function _beforeSuite($settings = array())
    {
        $this->apikey = $settings["modules"]["config"]["WebDriver"]["capabilities"]["apikey"];
    }

    // HOOK: before test
    public function _before(\Codeception\TestCase $test)
    {
        $this->stepPrefix = $test->getMetadata()->getFeature();
        $this->sessionId = $this->getModule('WebDriver')->webDriver->getSessionID();
        $this->requirementID = $test->getMetadata()->getName();
    }

    // HOOK: after test
    public function _after(\Codeception\TestCase $test)
    {
        $this->markSessionDetails($this->sessionStatus ? "Passed" : "Failed", $this->requirementID, $this->stepsStatus);
    }

    // HOOK: after each step
    public function _afterStep(\Codeception\Step $step)
    {
        $this->stepsStatus[$this->stepPrefix . ": " . $step->getPrefix() . $step->getAction() . " " . $step->getArgumentsAsString()] = $step->hasFailed() ? "Failed" : "Passed";
        $this->sessionStatus = $this->sessionStatus && !$step->hasFailed();
    }

    public function markSession($state)
    {
        if ($this->sessionId != null && strlen($this->sessionId) > 0) {
            $postData = array(
                'sessionID' => $this->sessionId,
                'sessionStatus' => $state,
                'apiKey' => $this->apikey
            );
            $this->sendResults($postData);
        } else {
            echo "Asayer: You have to initiate the AsayerWebDriver first in order to call markSession.\n";
        }
    }

    public function markSessionDetails($state, $requirementID, $testStatus)
    {
        if ($this->sessionId != null && strlen($this->sessionId) > 0) {
            if ($requirementID != null && strlen($requirementID) > 0 && count($testStatus) > 0) {
                $postData = array(
                    'sessionID' => $this->sessionId,
                    'sessionStatus' => $state,
                    'apiKey' => $this->apikey,
                    'reqID' => $requirementID,
                    'testStatus' => $testStatus
                );
                $this->sendResults($postData);
            } else {
                echo "Asayer: check the requirementID and the testStatus values.\n";
            }
        } else {
            echo "Asayer: You have to initiate the AsayerWebDriver first in order to call markSession.\n";
        }
    }

    private function sendResults($results)
    {
        $ch = curl_init('https://dashboard.asayer.io/mark_session');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($results)
        ));
        $response = curl_exec($ch);
    }

}

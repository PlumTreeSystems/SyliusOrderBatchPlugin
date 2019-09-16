<?php


namespace Tests\PTS\SyliusOrderBatchPlugin\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\MinkContext;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use PHPUnit\Framework\Assert;
use Tests\PTS\SyliusOrderBatchPlugin\Behat\Page\JQueryHelper;

class ExtendedPageContext extends MinkContext implements Context
{

    private $document;


    /**
     * @Then I am on :channel_name homepage
     * @param string $channel_name
     */
    public function iAmOnChannelHomepage(string $channel_name)
    {
        $value = $this->getSession()->getDriver()->getClient()
            ->getCrawler()->filter('img')->attr('alt');
        switch($channel_name)
        {
            case 'distributor':
                Assert::assertSame($value, "Sylius Distributor logo");
                break;
            case 'customer':
                Assert::assertSame($value, "Sylius logo");
                break;
            default:
                throw new PendingException();
        }
    }


    /**
     * @When I specify :field as :value
     * @param $field
     * @param $value
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iSpecifyField($field, $value)
    {
//        $crawler = $this->getSession()->getDriver()->getClient()->getCrawler();
//        /** @var Crawler $crawler */
//        $input = $crawler->filter('#distributor_threshold_amount')->first();
//        $input->getNode(0)->setAttribute('value', '156');
        $this->getDocument()->fillField($field, $value);
//        $asd = $crawler->selectButton('Save')->form();
//        $asd['distributor_threshold[amount]']->setValue('175');
//        $boop = 5;
    }

    /**
     * @When I submit the form
     */
    public function iSubmitForm()
    {
        $this->getDocument()->pressButton('Save');
    }

    private function getDocument()
    {
        if (null === $this->document) {
            $this->document = new DocumentElement($this->getSession());
        }

        return $this->document;
    }

    /**
     * @When /^I wait for a second$/
     */
    public function waitASecond()
    {
        JQueryHelper::wait($this->getSession());
    }

    /**
     * @When /^I capture a screenshot$/
     */
    public function captureScreenshot()
    {
        file_put_contents('/var/www/html/etc/build/special-'.md5(microtime()).'.png', $this->getSession()->getDriver()->getScreenshot());
    }

    /**
     * @When /^(?:|I )remove filter$/
     */
    public function removeFilter()
    {
        $script = "$('.filter-tag > label > i').first().click()";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @When /^(?:|I )search for a "(?P<value>(?:[^"]|\\")*)" product$/
     */
    public function searchField($value)
    {
        $field = $this->getSession()->getPage()->find('css', 'input.search[autocomplete="off"]');
        $field->setValue($value);
        $field->click();
        $result = $this->waitForElement(50, 'div.item[data-value="'.strtoupper($value).'"]');
        if (!$result) {
            throw new ElementNotFoundException($this->getSession()->getDriver());
        }
        JQueryHelper::wait($this->getSession());
        $div = $this->getSession()->getPage()->find('css', 'div.item[data-value="'.strtoupper($value).'"]');
        $div->click();
    }

    private function waitForElement($timeout, $selector)
    {
        return $this->getDocument()->waitFor($timeout, function () use ($selector) {
            return $this->hasElement($selector);
        });
    }

    private function hasElement($selector)
    {
        return $this->getDocument()->has('css', $selector);
    }

    /**
     * @When /^(?:|I )search for a "(?P<value>(?:[^"]|\\")*)" filter$/
     */
    public function searchFilter($value)
    {
        $field = $this->fixStepArgument('Filter search');
        $value = $this->fixStepArgument($value);
        $this->getSession()->getPage()->fillField($field, $value);
    }

    /**
     * @When /^(?:|I )select first option from autocomplete$/
     */
    public function selectFromAutoComplete()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
            $script = "$('#filtersSearch').trigger({ type : 'keydown', which : '' });";
            $this->getSession()->evaluateScript($script);

            JQueryHelper::wait($this->getSession());
            $script = "$('.ui-menu-item > div').first().click();";
            $this->getSession()->evaluateScript($script);

            $field = $this->fixStepArgument('Filter search');
            $value = $this->fixStepArgument('');
            $this->getSession()->getPage()->fillField($field, $value);
        }
    }

    /**
     * @When /^(?:|I )pass "([^"]+)" html to quill form field$/
     */
    public function passDataToQuill($html)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
            $script = "$('.ql-editor').append('" . $html . "');";
            $this->getSession()->evaluateScript($script);
        }
    }

    /**
     * Presses button with specified id|name|title|alt|value after request
     * Example: When I press "Log In" after request
     * Example: And I press "Log In" after request
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" when data is loaded$/
     */
    public function pressButtonWhenLoaded($button)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
            $button = $this->fixStepArgument($button);
            $this->getSession()->getPage()->pressButton($button);
        } else {
            throw new \Exception('context must be used with selenium');
        }
    }

    /**
     * Checks, that page contains specified text
     * Example: Then I should see "Who is the Batman?" after request
     * Example: And I should see "Who is the Batman?" after request
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" after request$/
     */
    public function assertPageContainsTextAfterRequest($text)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
            $this->assertSession()->pageTextContains($this->fixStepArgument($text));
        } else {
            throw new \Exception('context must be used with selenium');
        }
    }

    /**
     * Fills in form field with specified id|name|label|value
     * Example: When I fill in "username" with: "bwayne"
     * Example: And I fill in "bwayne" for "username"
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" when data is loaded$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with: when data is loaded$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" when data is loaded$/
     */
    public function fillFieldWhenLoaded($field, $value)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
            $field = $this->fixStepArgument($field);
            $value = $this->fixStepArgument($value);
            $this->getSession()->getPage()->fillField($field, $value);
        } else {
            throw new \Exception('context must be used with selenium');
        }
    }

    /**
     * @When I attach the file :path when data is loaded
     */
    public function iAttachImageWithTypeWhenLoaded($path)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $image = '/usr/local/images/' . $path;
        } else {
            $filesPath = $this->filesPath;

            $image = $filesPath . $path;
        }

        $this->getSession()->getPage()->find('css', 'input[type="file"]')->attachFile($image);

    }

    public function clickLink($link)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
        }
        parent::clickLink($link);
    }

    /**
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" inside the checkout box$/
     * @param $link
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function clickLinkInsideBox($link)
    {
        $this->clickLinkInsideElement($link, 'div.five.wide.column');
    }

    /**
     * @When I click on the autoship checkbox
     */
    public function clickOnAutoshipCheckbox()
    {
        $this->clickElement('div.ui.toggle.checkbox');
    }

    public function clickLinkInsideElement($link, $cssSelector)
    {
        $box = $this->getSession()->getPage()->find('css', $cssSelector);
        $box->clickLink($link);
    }

    /**
     * @When I click on element with css :cssSelector
     * @param $cssSelector
     */
    public function clickElement($cssSelector)
    {
        $box = $this->getSession()->getPage()->find('css', $cssSelector);
        $box->click();
    }

    /**
     * Presses button with specified id|name|title|alt|value
     * Example: When I press save in new batch modal
     * Example: And I press save in new batch modal
     *
     * @When /^(?:|I )press save in new batch modal$/
     */
    public function pressSaveInNewBatchModal()
    {
        $button = $this->fixStepArgument('confirmation-new-batch-button');
        $this->getSession()->getPage()->pressButton($button);
    }

    /**
     * Example: When I press element on save filter button
     * Example: And I press element on save filter button
     *
     * @When /^(?:|I )press element on save filter button$/
     */
    public function clickDiv()
    {
        $element = $this->getSession()->getPage()->find('css', '#saveFilterButton');

        if (empty($element)) {
            throw new \Exception("Save filter button not found");
        }

        $element->click();
    }

    /**
     * @Then I should see :customer with a special color
     */
    public function iShouldSeeWithASpecialColor($customer)
    {
        $element = $this->getSession()->getPage()->find('css', 'tr:contains("'.$customer.'")');
        $style = $element->getAttribute('style');
        Assert::assertNotNull($style);
        Assert::assertContains('background-color: #cbc2de', $style);
    }

    /**
     * @Then I should not see :customer with a special color
     */
    public function iShouldNotSeeWithASpecialColor($customer)
    {
        $element = $this->getSession()->getPage()->find('css', 'tr:contains("'.$customer.'")');
        $style = $element->getAttribute('style');
        if ($style) {
            Assert::assertNotContains('background-color: #cbc2de', $style);
        } else {
            Assert::assertNull($style);
        }
    }

    /**
     * Clicks link with specified id|title|alt|text
     * Example: When I follow "Log In"
     * Example: And I follow "Log In"
     *
     * @When /^(?:|I )follow "([^"]+)" option in table$/
     */
    public function clickLinkWithOffset($number)
    {
        $area = $this->getSession()->getPage()->find('css', 'tr:nth-child(' . $number . ')');
        $area->clickLink('Edit');
    }

    /**
     * @Given /^I wait for confirmation$/
     */public function iWaitForConfirmation()
{
    JQueryHelper::waitForAsynchronousActionsToFinish($this->getSession());
}

    /**
     * Fills in form field with specified id|name|label|value
     * Example: When I fill in "username" autocomplete field with: "bwayne"
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" autocomplete field with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" autocomplete field with:$/
     */
    public function fillAutocompleteField($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $this->getSession()->getPage()->fillField($field, $value);

        $result = $this->waitForElement(20, 'div.autocompleteItem');
        if (!$result) {
            throw new ElementNotFoundException($this->getSession()->getDriver());
        }
        JQueryHelper::wait($this->getSession());
    }



    /**
     * Presses button with specified id|name|title|alt|value
     * Example: When I press "Log In"
     * Example: And I press "Log In"
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in autocomplete$/
     */
    public function pressAutocompleteButton($button)
    {
        $result = $this->waitForElement(100, 'div.autocomplete-items input[value="Add to autoship"]');

        if (!$result) {
            throw new ElementNotFoundException($this->getSession()->getDriver());
        }

        $this->waitASecond();

        $button= $this->getSession()->getPage()->find('css', 'div.autocomplete-items input[value="Add to autoship"]');

        $button->click();
    }

    /**
     * Click on text
     *
     * @When /^I click on the text "([^"]*)"$/
     */
    public function iClickOnTheText($text)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//*/text()[normalize-space(.)="'. $text .'"]/parent::*')
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
        }

        $element->click();
    }


    /**
     * Select option from js select
     *
     * @When /^I select "([^"]*)" from options$/
     */
    public function selectFromOptions($text)
    {
        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('css', '.select_display')
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find select'));
        }
        $element->click();

        $session = $this->getSession();
        $element = $session->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', '//*/text()[normalize-space(.)="'. $text .'"]/parent::*')
        );

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Cannot find text: "%s"', $text));
        }

        $element->click();
    }
}

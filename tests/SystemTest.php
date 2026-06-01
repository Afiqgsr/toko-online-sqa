<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SystemTest extends TestCase
{
    private $driver;
    private $baseUrl = 'http://localhost:8000';

    protected function setUp(): void
    {
        $host = 'http://localhost:9515';

        $chromeOptions = new ChromeOptions();

        // CI-safe arguments
        $chromeOptions->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage'
        ]);

        // IMPORTANT for GitHub Actions Chrome
        $chromeOptions->setBinary('/usr/bin/google-chrome');

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    public function testHomepageAndSearchFeature()
    {
        // Buka aplikasi
        $this->driver->get($this->baseUrl);

        // WAIT: halaman siap
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::tagName('body')
            )
        );

        // Validasi homepage
        $bodyText = $this->driver
            ->findElement(WebDriverBy::tagName('body'))
            ->getText();

        $this->assertStringContainsString('Toko Online', $bodyText);

        // WAIT: search box ready
        $searchBox = $this->driver->wait(10)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('cari')
            )
        );

        // aksi search
        $searchBox->sendKeys('Kemeja');
        $searchBox->submit();

        // WAIT: hasil pencarian muncul
        $this->driver->wait(10)->until(
            WebDriverExpectedCondition::textToBePresentInElement(
                WebDriverBy::tagName('body'),
                'Kemeja Flanel'
            )
        );

        $updatedBodyText = $this->driver
            ->findElement(WebDriverBy::tagName('body'))
            ->getText();

        $this->assertStringContainsString('Kemeja Flanel', $updatedBodyText);
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}
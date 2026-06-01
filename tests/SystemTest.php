<?php
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;

class SystemTest extends TestCase{
    private $driver;
    private $baseUrl = 'http://localhost:8000';

    protected function setUp(): void{
        // Mengarahkan ke server ChromeDriver lokal / GitHub Actions
        $host = 'http://localhost:9515';

        $chromeOptions = new ChromeOptions();
        // Mode Headless sangat krusial agar tidak error saat berjalan di GitHub Actions
        $chromeOptions->addArguments(['--headless', '--disable-gpu', '--no-sandbox']);
        //$chromeOptions->setBinary(
        //'D:\\Tugas Kuliah\\Semester 6\\Penjaminan Perangkat Lunak\\Minggu 12\\chrome-win64\\chrome-win64\\chrome.exe');
        $chromeOptions->setBinary('/usr/bin/google-chrome');

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->driver = RemoteWebDriver::create($host, $capabilities);
        
    }

    public function testHomepageAndSearchFeature(){
        // Kunjungi server lokal
        $this->driver->get($this->baseUrl);

        // Validasi antarmuka memuat teks judul
        $bodyText = $this->driver->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertStringContainsString('Toko Online', $bodyText);

        // Simulasi pengguna mencari barang
        $searchBox = $this->driver->findElement(WebDriverBy::name('cari'));
        $searchBox->sendKeys('Kemeja');
        $searchBox->submit(); // Tekan enter

        // Validasi hasil pencarian
        $updatedBodyText = $this->driver->findElement(WebDriverBy::tagName('body'))->getText();
        $this->assertStringContainsString('Kemeja Flanel', $updatedBodyText);
    }

    protected function tearDown(): void{
        if ($this->driver) {
            $this->driver->quit(); // Tutup browser
        }
    }

}
<?php

use Carbon\Carbon;
use Inquid\Stock\Tests\TestCase;

class StockDatesTest extends TestCase
{
    /** @test */
    public function it_can_create_stock_dates()
    {
        Carbon::setTestNow('2020-01-01 00:00:00');
        $this->stockModel->setStock(10);
        
        Carbon::setTestNow('2020-01-02 00:00:00');
        $this->stockModel->increaseStock(20);
        
        Carbon::setTestNow('2020-01-03 00:00:00');
        $this->stockModel->decreaseStock(5);
        
        $this->assertEquals(25, $this->stockModel->stock());
        $this->assertEquals(10, $this->stockModel->stock('2020-01-01 00:00:00'));
    }
}
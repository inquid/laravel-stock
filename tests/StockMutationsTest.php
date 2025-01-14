<?php

namespace Inquid\Stock\Tests;

use Inquid\Stock\Tests\Models\StockModel;

class StockMutationsTest extends TestCase
{
    /** @test */
    public function it_can_have_no_mutations()
    {
        $this->assertEmpty($this->stockModel->stockMutations->toArray());
    }

    /** @test */
    public function it_can_have_some_mutations()
    {
        $this->stockModel->increaseStock(10);
        $this->stockModel->increaseStock(1);
        $this->stockModel->decreaseStock(1);

        $mutations = $this->stockModel->stockMutations->pluck(['amount'])->toArray();

        $this->assertEquals(['10.0', '1.0', '-1.0'], $mutations);
    }

    /** @test */
    public function it_has_positive_mutations_after_setting_stock()
    {
        $this->stockModel->increaseStock(5);
        $this->stockModel->setStock(10);

        $mutations = $this->stockModel->stockMutations->pluck(['amount'])->toArray();

        $this->assertEquals(['5.0', '5.0'], $mutations);
    }

    /** @test */
    public function it_has_mixed_mutations_after_setting_stock()
    {
        $this->stockModel->clearStock(10);
        $this->stockModel->setStock(5);

        $mutations = $this->stockModel->stockMutations->pluck(['amount'])->toArray();

        $this->assertEquals(['10.0', '-5.0'], $mutations);
    }

    /** @test */
    public function it_can_have_mutations_with_description()
    {
        $this->stockModel->increaseStock(10, [
            'description' => 'Test',
        ]);

        $mutations = $this->stockModel->stockMutations->pluck(['description'])->toArray();

        $this->assertEquals(['Test'], $mutations);
    }

    /** @test */
    public function it_can_have_mutations_with_reference()
    {
        $this->stockModel->increaseStock(10, [
            'reference' => $this->referenceModel,
        ]);

        $stockMutation = $this->stockModel->stockMutations->first();
        $referenceMutation = $this->referenceModel->stockMutations->first();

        $this->assertSame(1, $stockMutation->reference_id);
        $this->assertSame(ReferenceModel::class, $stockMutation->reference_type);
        $this->assertSame(1, $referenceMutation->stockable_id);
        $this->assertSame(StockModel::class, $referenceMutation->stockable_type);
    }
}

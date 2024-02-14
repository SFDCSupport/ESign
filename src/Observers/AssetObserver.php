<?php

namespace NIIT\ESign\Observers;

use NIIT\ESign\Models\Asset;

class AssetObserver extends Observer
{
    public function creating(Asset $asset): void
    {
    }
}

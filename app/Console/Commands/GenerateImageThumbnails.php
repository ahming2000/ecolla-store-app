<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('images:generate-thumbnails')]
#[Description('Generate missing WebP thumbnails for non-receipt images')]
class GenerateImageThumbnails extends Command
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Image::query()
            ->whereNull('thumbnail_id')
            ->whereDoesntHave('originalImage')
            ->whereDoesntHave('order')
            ->orderBy('id');
        $total = (clone $query)->count();

        if ($total === 0) {
            $this->components->info('All eligible images already have thumbnails.');

            return self::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setMessage('Generating image thumbnails');
        $progressBar->start();

        try {
            $query->lazyById()->each(function (Image $image) use (
                $progressBar,
            ): void {
                $this->imageService->generateThumbnail($image);
                $progressBar->advance();
            });
        } catch (Throwable $exception) {
            $progressBar->finish();
            $this->newLine(2);
            report($exception);
            $this->components->error(
                "Unable to generate thumbnail: {$exception->getMessage()}",
            );

            return self::FAILURE;
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->components->info(
            "Generated {$total} image thumbnails.",
        );

        return self::SUCCESS;
    }
}

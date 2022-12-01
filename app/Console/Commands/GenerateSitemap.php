<?php

namespace App\Console\Commands;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    private const MODELS = [
        'Page'          => '',
        'Service'       => 'services',
        'News'          => 'news',
        'Category'      => 'category',
        'Institution'   => 'institutions',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemaps';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        foreach (self::MODELS as $model => $type) {
            $this->generate('App\Models\\' . $model, $type, $sitemap);
        }

        $this->generateReports($sitemap);
        $this->generateReportsYears($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));

        return Command::SUCCESS;
    }

    public function generate($model, $type, &$sitemap)
    {
        $model = app($model);
        $items = $model->select('*')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now()->toDateTimeString())
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($items as $item) {
            // если в модели есть поле is_published и оно не равно 1 - пропускаем
            if (!($item->is_published ?? 1)) {
                continue;
            }

            $uri = $type
                ? "{$type}/{$item->uri}"
                : $item->uri;
            $sitemap->add(Url::create($uri)
                ->setLastModificationDate($item->updated_at ?: Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.1)
                ->addAlternate($uri, 'uk-UA')
                ->addAlternate("/en/{$uri}", 'en')
            );
        }
    }

    public function generateReports(&$sitemap)
    {
        $items = Report::select('filename', 'updated_at')
            ->whereNotNull('published_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($items as $item) {
            $uri = 'storage/' . $item->filename;
            $sitemap->add(Url::create($uri)
                ->setLastModificationDate($item->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.1)
            );
        }
    }

    public function generateReportsYears(&$sitemap)
    {
        $items = Report::select('year')
            ->whereNotNull('published_at')
            ->orderBy('year', 'desc')
            ->groupBy('year')
            ->get();

        foreach ($items as $item) {
            $uri = 'reports/' . $item->year;
            $sitemap->add(Url::create($uri)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.1)
            );
        }
    }
}

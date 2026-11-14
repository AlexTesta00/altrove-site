<?php
declare(strict_types=1);
namespace Altrove;
final class Seo {
    public static function graph(array $config, array $page, string $path): array {
        $base = $config['url'];
        $person = ['@type'=>'Person','@id'=>$base.'/#alessia','name'=>'Alessia Ventura','url'=>$base.'/alessia-ventura/','jobTitle'=>'Content creator e autrice di Altrove Podcast','description'=>'Content creator, esperta di comunicazione per i brand e ideatrice di Altrove Podcast.','disambiguatingDescription'=>'Content creator e ideatrice di Altrove Podcast, il podcast italiano sulle esperienze di pre-morte, la medianità e la spiritualità','sameAs'=>['https://www.instagram.com/alessia.ventura23/','https://www.instagram.com/altrove_podcast/','https://www.youtube.com/@altrovepodcast'],'knowsAbout'=>['Content creation','Podcast','Esperienze di pre-morte','Medianità','Channeling','Ipnosi']];
        $graph = [
            ['@type'=>'WebSite','@id'=>$base.'/#sito','name'=>'Altrove Podcast','url'=>$base.'/','inLanguage'=>'it-IT','publisher'=>['@id'=>$base.'/#alessia']],
            $person,
            ['@type'=>'PodcastSeries','@id'=>$base.'/#podcast','name'=>'Altrove Podcast','description'=>'Storie oltre il visibile, con Alessia Ventura.','url'=>'https://www.youtube.com/@altrovepodcast','inLanguage'=>'it-IT','author'=>['@id'=>$base.'/#alessia']]
        ];
        if ($path !== '/' && $page['id'] !== '404') $graph[] = ['@type'=>'BreadcrumbList','itemListElement'=>[
            ['@type'=>'ListItem','position'=>1,'name'=>'Altrove','item'=>$base.'/'],
            ['@type'=>'ListItem','position'=>2,'name'=>$page['h1'],'item'=>$base.$path]
        ]];
        if (!empty($page['faq'])) $graph[] = ['@type'=>'FAQPage','mainEntity'=>array_map(static fn($faq)=>['@type'=>'Question','name'=>$faq[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$faq[1]]],$page['faq'])];
        return ['@context'=>'https://schema.org','@graph'=>$graph];
    }
}


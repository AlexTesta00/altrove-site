<?php
declare(strict_types=1);
namespace Altrove;
final class WhatsAppMessage {
    public static function build(array $data): array {
        $roles=['esperienza'=>"Ho vissuto un'esperienza",'professionista'=>'Sono un professionista','progetto'=>'Ho un progetto da proporre'];
        $themes=['nde'=>['🕊️','Esperienze di pre-morte'],'medium'=>['🔮','Medium e sensitivi'],'channeling'=>['🌌','Channeling e coscienza stellare'],'mentalismo'=>['🌀','Mentalismo e ipnosi'],'altro'=>['✨','Altro']];
        [$emoji,$theme]=$themes[$data['tema']]??$themes['altro'];
        $blocks=[
            ['title'=>'🌙 Candidatura · ALTROVE Podcast','value'=>''],
            ['title'=>'','value'=>'Ciao Alessia! Ecco la mia candidatura per Altrove e la mia storia 👇'],
            ['title'=>'👤 Nome e cognome','value'=>$data['nome'].' '.$data['cognome']],
            ['title'=>'🎙️ Chi sono','value'=>$roles[$data['veste']]??$roles['esperienza']],
            ['title'=>$emoji.' Tema','value'=>$theme],
            ['title'=>'📖 La mia storia','value'=>$data['esperienza']],
            ['title'=>'📋 Piano scelto','value'=>ParticipationPlans::message($data['piano'] ?? null)],
            ['title'=>'📞 Telefono','value'=>$data['telefono']],
            ['title'=>'','value'=>Privacy::CONSENT . ' Informativa: ' . Privacy::VERSION . '.'],
            ['title'=>'','value'=>"Grazie per l'ascolto ✨"],
        ];
        $text=implode("\n\n",array_map(static function(array $block): string {
            if($block['title']==='')return $block['value'];
            $split=mb_strpos($block['title'],' ');
            return mb_substr($block['title'],0,$split).' *'.mb_substr($block['title'],$split+1).'*'.($block['value']!==''?"\n".$block['value']:'');
        },$blocks));
        return ['blocks'=>$blocks,'text'=>$text,'copy_required'=>strlen(rawurlencode($text))>6000];
    }
}

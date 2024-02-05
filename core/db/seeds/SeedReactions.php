<?php

use App\Models\Reaction;
use Phinx\Seed\AbstractSeed;

class SeedReactions extends AbstractSeed
{
    public function run(): void
    {
        $reactions = [
            'Thumb Up' => '👍',
            'Thumb Down' => '👎',
            'Heart' => '❤️',
            'Fire' => '🔥',
            'Surprise' => '😮',
            'Cry' => '😢',
            'Smile' => '😀',
            'Angry' => '😡',
        ];

        foreach ($reactions as $title => $emoji) {
            if (!Reaction::query()->where('title', $title)->count()) {
                $reaction = new Reaction(['title' => $title, 'emoji' => $emoji]);
                $reaction->save();
            }
        }

        $reactions = Reaction::query()->orderBy('rank')->orderBy('id');
        foreach ($reactions->cursor() as $idx => $reaction) {
            $reaction->update(['rank' => $idx]);
        }
    }
}

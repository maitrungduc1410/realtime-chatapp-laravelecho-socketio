<?php

use Illuminate\Database\Seeder;
use App\Emoji;

class EmojiTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chatroom1 = new Emoji();
        $chatroom1->name = 'Love';
        $chatroom1->value = 'love';
        $chatroom1->src = '/images/emoji/love.png';
        $chatroom1->alt = '😍';
        $chatroom1->save();

        $chatroom2 = new Emoji();
        $chatroom2->name = 'Haha';
        $chatroom2->value = 'haha';
        $chatroom2->src = '/images/emoji/haha.png';
        $chatroom2->alt = '😆';
        $chatroom2->save();
        
        $chatroom3 = new Emoji();
        $chatroom3->name = 'Wow';
        $chatroom3->value = 'wow';
        $chatroom3->src = '/images/emoji/wow.png';
        $chatroom3->alt = '😮';
        $chatroom3->save();

        $chatroom4 = new Emoji();
        $chatroom4->name = 'Cry';
        $chatroom4->value = 'cry';
        $chatroom4->src = '/images/emoji/cry.png';
        $chatroom4->alt = '😢';
        $chatroom4->save();

        $chatroom6 = new Emoji();
        $chatroom6->name = 'Angry';
        $chatroom6->value = 'angry';
        $chatroom6->src = '/images/emoji/angry.png';
        $chatroom6->alt = '😠';
        $chatroom6->save();

        $chatroom5 = new Emoji();
        $chatroom5->name = 'Like';
        $chatroom5->value = 'like';
        $chatroom5->src = '/images/emoji/like.png';
        $chatroom5->alt = '👍';
        $chatroom5->save();

        $chatroom7 = new Emoji();
        $chatroom7->name = 'Dislike';
        $chatroom7->value = 'dislike';
        $chatroom7->src = '/images/emoji/dislike.png';
        $chatroom7->alt = '👎';
        $chatroom7->save();
    }
}

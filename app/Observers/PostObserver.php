<?php

namespace App\Observers;

use App\Jobs\AddPostToCache;
use App\Jobs\DeletePostFromCache;
use App\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function created(Post $post)
    {
        $this->savePostToCache($post);
    }

    /**
     * Handle the Post "updated" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        $this->savePostToCache($post);
    }

    /**
     * Handle the Post "deleted" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function deleted(Post $post)
    {
        $this->removePostFromCache($post);
    }

    /**
     * Creates/Updates the Post to cache.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    protected function savePostToCache(Post $post)
    {
        dispatch(new AddPostToCache([
            'id' => $post->id,
            'user_id' => $post->user_id,
            'title' => $post->title,
            'username' => $post->user->username,
            'created_at' => $post->created_at,
        ]));
    }

    /**
     * Deletes the Post from cache.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    protected function removePostFromCache(Post $post)
    {
        dispatch(new DeletePostFromCache($post->id));
    }
}

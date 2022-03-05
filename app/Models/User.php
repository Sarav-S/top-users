<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    private $limit = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'stats',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'stats' => 'array',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'DESC');
    }

    /**
     * Calculates the top users who has posted a minimum
     * of the 10 posts for last 7 days
     *
     * @param integer $limit 10
     * @param integer $days 7
     * @return array
     */
    public function topUsers($limit = 10, $days = 7): array
    {
        $date = now()->subDays($days)->format('Y-m-d');

        // $records = Post::select('user_id', DB::raw('count(id) as posts_count'), DB::raw('max(id) as last_id'))
        //     ->whereDate('created_at', '>', now()->subDays($days)->format('Y-m-d'))
        //     ->groupBy('user_id')
        //     ->having(DB::raw('count(id)'), '>', $limit)
        //     ->get();

        // Postgres's "having" method threw error. So, considering
        // time constraints I wrote raw query.

        $records = DB::select('
            SELECT
                    "user_id",
                    count(*) as posts_count,
                    max(id) as last_id
            FROM
                    "posts"
            WHERE
                    "created_at"::date > \'' . $date . '\'
            GROUP BY
                    "user_id"
            HAVING count(*) > ' . $limit);

        if (!count($records)) {return [];}

        $usersIds = collect($records)->map(function ($record) {return $record->user_id;});
        $postsIds = collect($records)->map(function ($record) {return $record->last_id;});

        $users = User::whereIn('id', $usersIds)->pluck('username', 'id');
        $posts = Post::whereIn('id', $postsIds)->pluck('title', 'id');

        $topUsersData = [];
        foreach ($records as $record) {
            array_push($topUsersData, [
                'username' => $users[$record->user_id],
                'total_posts_count' => $record->posts_count,
                'last_post_title' => $posts[$record->last_id],
            ]);
        }

        return $topUsersData;
    }

    /**
     * Returns the frequent posters
     *
     * @param integer $limit
     * @return array
     */
    public function frequentPosters($limit = 10): array
    {
        return collect(cache()->get('top_users_data_redis'))
            ->groupBy('user_id')
            ->filter(function ($record) use ($limit) {
                return $record->count() > $limit;
            })
            ->map(function ($data) {
                $record = $data->last();
                return [
                    'username' => $record['username'],
                    'total_posts_count' => $data->count(),
                    'last_post_title' => $record['title'],
                ];
            })->values()->toArray();
    }
}

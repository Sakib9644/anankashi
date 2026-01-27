<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = ['api', 'web'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'last_activity_at',
        'slug',
        'verification_token',
        'otp_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
        'is_online',
        'balance'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'otp_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    public function getIsOnlineAttribute()
    {
        return $this->last_activity_at > now()->subMinutes(5);
    }

    public function getBalanceAttribute()
    {
        $increment = $this->transactions()->where('type', 'increment')->sum('amount');
        $decrement = $this->transactions()->where('type', 'decrement')->sum('amount');
        return $increment - $decrement;
    }

    public function getRoleAttribute()
    {
        return  $this->getRoleNames()->first();
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseTokens::class);
    }

    public function profile() {
        return $this->hasOne(Profile::class);
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    //chat related methods
    public function senders()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function roomsAsUserOne()
    {
        return $this->hasMany(Room::class, 'user_one_id');
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class, 'user_two_id');
    }

    public function allRooms()
    {
        return Room::where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
    }

}

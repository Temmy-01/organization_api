<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Notifications\User\ResetPassword;
use App\Notifications\User\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'user_type_id',
        'password', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // public function customer()
    // {
    //     return $this->hasOne(Customer::class);
    // }
    public function base_user()
    {
        return $this->hasOne(BaseUser::class, 'user_id', 'id');
    }
    public function subAccounts()
    {
        return $this->hasOne(SubAccount::class, 'user_id', 'id');
    }

    public function accounts()
    {
        return $this->hasOne(Account::class, 'user_id', 'id');
    }



    public $appends = ['profile_picture'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Gets full URL for post profile picture.
     *
     * @return string
     */
    public function getProfilePictureAttribute()
    {
        return $this->getFirstMediaUrl(MediaCollection::PROFILEPICTURE);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $callbackUrl = config('frontend.user.url');

        $this->notify(new VerifyEmail($callbackUrl));
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $callbackUrl = config('frontend.user.url');
        // $callbackUrl = request('callbackUrl', config('frontend.user.url'));

        $this->notify(new ResetPassword($callbackUrl, $token));
    }

    /**
     * Registers media collections
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::PROFILEPICTURE)
            ->useFallbackUrl(url('/images/profile-picture-placeholder.jpg'))
            ->singleFile();
    }

    /**
     * Get the country the user model belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Get the state the model belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    /**
     * Get the city the model belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Get the user's likes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the user's posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function posts()
    {
        return $this->morphMany(Post::class, 'postable');
    }

    /**
     * Get the user's advert subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function advertSubscriptions()
    {
        return $this->hasMany(Advert::class);
    }

    /**
     * Get the user's created events.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'creator');
    }

    /**
     * Get the user's "active/running" advert.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function activeAdvertSubscriptions()
    {
        return $this->advertSubscriptions()
            ->terminated(false)
            ->paused(false)
            ->where('end_date', '>=', now()->toDateTimeString())
            ->latest();
    }
}

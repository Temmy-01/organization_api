<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\MediaCollection;
use App\Notifications\User\ResetPassword;
use App\Notifications\User\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class BaseUser extends Model
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    // use SoftDeletes;
    // use InteractsWithMedia;
    use HasRoles;
    protected $guarded = [];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subAccounts()
    {
        return $this->hasOne(SubAccount::class, 'base_user_id', 'id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

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
        $callbackUrl = request('callbackUrl', config('frontend.user.url'));

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
        $callbackUrl = request('callbackUrl', config('frontend.user.url'));

        $this->notify(new ResetPassword($callbackUrl, $token));
    }
}

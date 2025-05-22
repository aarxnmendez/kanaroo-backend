<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest as BaseEmailVerificationRequest;
use Illuminate\Support\Facades\Log; // For logging.

class CustomEmailVerificationRequest extends BaseEmailVerificationRequest
{
    /**
     * Get the user for the email verification request.
     *
     * @param  string|null  $guard
     * @return mixed
     */
    public function user($guard = 'web') // Override base user method.
    {
        $routeId = $this->route('id');
        $userFromAuthGuard = $this->container['auth']->guard($guard)->user();
        $userFromProvider = null;

        if (is_null($userFromAuthGuard)) {
            // This is expected for a route without auth:sanctum middleware.
            $userFromProvider = $this->container['auth']->guard($guard)->getProvider()->retrieveById($routeId);
        }

                Log::debug('CustomEmailVerificationRequest::user() called', [
            'routeId' => $routeId,
            'userFromAuthGuard_is_null' => is_null($userFromAuthGuard),
            'userFromProvider_is_null' => is_null($userFromProvider),
            'resolved_user_object_from_provider' => $userFromProvider ? get_class($userFromProvider) : null,
            'user_id_from_provider' => $userFromProvider ? $userFromProvider->getKey() : 'User from provider is null or has no key'
        ]);

        return $userFromAuthGuard ?? $userFromProvider;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $userIdFromRoute = $this->route('id');
        // Ensure our overridden user() method is called and resolves a user.
        if (! $this->user()) {
            Log::warning('CustomEmailVerificationRequest::authorize() - User not found or could not be resolved by custom user() method.');
            return false;
        }

        // Perform authorization checks using the resolved user.
        if (! hash_equals((string) $this->route('id'),
                           (string) $this->user()->getKey())) {
            Log::warning('CustomEmailVerificationRequest::authorize() - User ID from route does not match resolved user key.', ['route_id' => $this->route('id'), 'user_key' => $this->user()->getKey()]);
            return false;
        }

        if (! hash_equals((string) $this->route('hash'),
                           sha1($this->user()->getEmailForVerification()))) {
            Log::warning('CustomEmailVerificationRequest::authorize() - Hash mismatch.', ['route_hash' => $this->route('hash'), 'expected_hash_for_email' => sha1($this->user()->getEmailForVerification())]);
            return false;
        }

        Log::info('CustomEmailVerificationRequest::authorize() - Authorization successful.');
        return true;
    }
}

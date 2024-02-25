<?php

namespace App\Policies;

use App\Models\User;
use App\Models\users\marketer;
use App\Models\users\serviceProvider;
use Illuminate\Auth\Access\HandlesAuthorization;

class marketerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, marketer $marketer)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, marketer $marketer)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, marketer $marketer)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, marketer $marketer)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\users\marketer  $marketer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, marketer $marketer)
    {
        //
    }

    public function add_sp(marketer $marketer , serviceProvider $sp){

       return !$marketer->serviceproviders->contains($sp);
    }
}

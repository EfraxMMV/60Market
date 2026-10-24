<?php

namespace App\Policies;

use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdvertisementPolicy
{
    /**
     * Determina si el usuario puede ver la lista de sus anuncios.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un anuncio específico.
     */
    public function view(User $user, Advertisement $advertisement): bool
    {
        return $advertisement->business && $advertisement->business->owner_user_id === $user->id;
    }

    /**
     * Determina si el usuario puede crear anuncios.
     */
    public function create(User $user): bool
    {
        // El usuario debe poseer al menos una empresa activa (D06, D08)
        return $user->businesses()->exists();
    }

    /**
     * Determina si el usuario puede editar el anuncio.
     * Solo el dueño puede editar, y solo si el anuncio está en estado 'draft' (D07, D08).
     */
    public function update(User $user, Advertisement $advertisement): Response
    {
        if (!$advertisement->business || $advertisement->business->owner_user_id !== $user->id) {
            return Response::deny('No estás autorizado para modificar este anuncio porque pertenece a otra empresa.');
        }

        if ($advertisement->status !== 'draft') {
            return Response::deny('Solo los anuncios en estado borrador pueden ser modificados. Los anuncios en cola o activos están bloqueados.');
        }

        return Response::allow();
    }

    /**
     * Determina si el usuario puede eliminar el anuncio.
     * Solo el dueño puede eliminar anuncios en estado 'draft' o 'cancelled'.
     */
    public function delete(User $user, Advertisement $advertisement): Response
    {
        if (!$advertisement->business || $advertisement->business->owner_user_id !== $user->id) {
            return Response::deny('No estás autorizado para eliminar este anuncio.');
        }

        if (!in_array($advertisement->status, ['draft', 'cancelled'])) {
            return Response::deny('No puedes eliminar un anuncio que ya está en cola o en emisión.');
        }

        return Response::allow();
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CorreoHomologacionEnviado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idpersona_facha_homologacion;
    public $correo;
    public $metadata;

    public function __construct($idpersona_facha_homologacion, $correo, $metadata = [])
    {
        $this->idpersona_facha_homologacion = $idpersona_facha_homologacion;
        $this->correo = $correo;
        $this->metadata = $metadata;
    }
}

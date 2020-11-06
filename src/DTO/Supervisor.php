<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @SWG\Schema()
 */
class Supervisor
{

    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Supervisor name")
     */
    public string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="bool")
     * @Serializer\Type("bool")
     * @SWG\Property(type="boolean", title="Running")
     */
    public bool $running;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="bool")
     * @Serializer\Type("bool")
     * @SWG\Property(type="boolean", title="Connected")
     */
    public bool $connected;

    /**
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Process id")
     */
    public ?int $pid = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Satus code")
     */
    public int $stateCode;

    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Status name")
     */
    public string $stateName;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Supervisor identification")
     */
    public string $identification;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="API version")
     */
    public string $apiVersion;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Supervisor version")
     */
    public string $supervisorVersion;

    /**
     * @var SupervisorProcess[]
     * @Serializer\Type("array<HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess>")
     * @SWG\Property(type="array",@SWG\Items(
     *                      ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess::class)
     *                  )), description="Collection of process", title="Processes")
     */
    public array $process = [];


    public static function mapSupervisor(\Supervisor\Supervisor $supervisor, string $supervisorKey): Supervisor
    {
        $self = new self();
        $self->running = $supervisor->isRunning();
        $self->connected = $supervisor->isConnected();
        $self->pid = $supervisor->getPID();
        $self->stateCode = $supervisor->getState()['statecode'] ?? 0;
        $self->stateName = $supervisor->getState()['statename'] ?? '';
        $self->identification = $supervisor->getIdentification();
        $self->apiVersion = $supervisor->getAPIVersion();
        $self->supervisorVersion = $supervisor->getSupervisorVersion();
        $self->name = $supervisorKey;

        foreach ($supervisor->getAllProcesses() as $process) {
            $self->process[] = SupervisorProcess::mapProcess($process);
        }

        return $self;
    }
}

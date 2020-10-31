<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SWG\Schema()
 */
class Supervisor
{
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
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Connected")
     */
    public int $pid;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Connected")
     */
    public int $stateCode;

    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $stateName;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $identification;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $apiVersion;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $supervisorVersion;

    /**
     * @var SupervisorProcess[]
     * @Serializer\Type("array<HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess>")
     */
    public array $process = [];


    public static function mapSupervisor(\Supervisor\Supervisor $supervisor): Supervisor
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

        foreach ($supervisor->getAllProcesses() as $process) {
            $self->process[] = SupervisorProcess::mapProcess($process);
        }

        return $self;
    }
}
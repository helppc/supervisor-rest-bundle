<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\DTO;

use JMS\Serializer\Annotation as Serializer;
use phpDocumentor\Reflection\Types\This;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SWG\Schema()
 */
class SupervisorProcess
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
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Group name")
     */
    public string $group;
    /**
     * @Serializer\Type("DateTimeImmutable")
     * @SWG\Property(type="string", format="datetime", title="Date of last start")
     */
    public ?\DateTimeImmutable $start = null;

    /**
     * @Serializer\Type("DateTimeImmutable")
     * @SWG\Property(type="string", format="datetime", title="Date of last stop")
     */
    public ?\DateTimeImmutable  $stop = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Status code")
     */
    public int $stateCode;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Status name")
     */
    public string $stateName;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Spawn error")
     */
    public string $spawnerr;
    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Exit status")
     */
    public int $exitStatus;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Path for log file")
     */
    public string $logfile;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Path for standard output file")
     */
    public string $stdoutLogFile;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Path for standard error file")
     */
    public string $stderrLogFile;

    /**
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Process id")
     */
    public ?int $pid = null;

    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Description")
     */
    public ?string $description = null;


    public static function mapProcess(\Supervisor\Process $process): SupervisorProcess
    {
        $self = new self();
        $self->running = $process->isRunning();
        $self->name = $process->getName();
        $payload = $process->getPayload();

        $self->group = $payload['group'];

        if (!empty($payload['start'])) {
            $self->start = new \DateTimeImmutable('@' . $payload['start']);
        }

        if (!empty($payload['stop'])) {
            $self->stop = new \DateTimeImmutable('@' . $payload['stop']);
        }

        $self->stateCode = $payload['state'];
        $self->stateName = $payload['statename'];
        $self->spawnerr = $payload['spawnerr'];
        $self->exitStatus = $payload['exitstatus'];
        $self->logfile = $payload['logfile'];
        $self->stdoutLogFile = $payload['stdout_logfile'];
        $self->stderrLogFile = $payload['stderr_logfile'];
        $self->pid = $payload['pid'];
        $self->description = $payload['description'];


        return $self;
    }
}
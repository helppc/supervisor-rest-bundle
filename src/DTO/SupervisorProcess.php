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
     * @SWG\Property(type="boolean", title="Connected")
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
     * @SWG\Property(type="string", title="Connected")
     */
    public string $group;
    /**
     * @Serializer\Type("DateTimeImmutable")
     * @SWG\Property(type="string", format="datetime", title="Date of creation")
     */
    public ?\DateTimeImmutable $start = null;
    /**
     * @Serializer\Type("DateTimeImmutable")
     * @SWG\Property(type="string", format="datetime", title="Date of creation")
     */
    public ?\DateTimeImmutable  $stop = null;
    /**
     * @Serializer\Type("DateTimeImmutable")
     * @SWG\Property(type="string", format="datetime", title="Date of creation")
     */
    public ?\DateTimeImmutable  $now = null;
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
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $spawnerr;
    /**
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Connected")
     */
    public int $exitstatus;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $logfile;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $stdoutLogFile;
    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
     */
    public string $stderrLogFile;

    /**
     * @Assert\Type(type="integer")
     * @Serializer\Type("integer")
     * @SWG\Property(type="integer", title="Connected")
     */
    public int $pid;

    /**
     * @Assert\Type(type="string")
     * @Serializer\Type("string")
     * @SWG\Property(type="string", title="Connected")
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

        if (!empty($payload['now'])) {
            $self->now = new \DateTimeImmutable('@' . $payload['now']);
        }
        $self->stateCode = $payload['state'];
        $self->stateName = $payload['statename'];
        $self->spawnerr = $payload['spawnerr'];
        $self->exitstatus = $payload['exitstatus'];
        $self->logfile = $payload['logfile'];
        $self->stdoutLogFile = $payload['stdout_logfile'];
        $self->stderrLogFile = $payload['stderr_logfile'];
        $self->pid = $payload['pid'];
        $self->description = $payload['description'];


        return $self;
    }
}
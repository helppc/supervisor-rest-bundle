<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\Controller;

use HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess;
use HelpPC\Bundle\SupervisorRestBundle\Manager\SupervisorManager;
use JMS\Serializer\SerializerInterface;
use Supervisor\Exception\SupervisorException;
use Supervisor\Supervisor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * SupervisorController
 */
class SupervisorController extends AbstractController
{
    private SupervisorManager $supervisorManager;
    private TranslatorInterface $translator;
    private SerializerInterface $serializer;

    public function __construct(SupervisorManager $supervisorManager, TranslatorInterface $translator, SerializerInterface $serializer)
    {
        $this->supervisorManager = $supervisorManager;
        $this->translator = $translator;
        $this->serializer = $serializer;
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="List of all supervisors",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", type="array",
     *                  @SWG\Items(
     *                      ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::class)
     *                  )
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function indexAction(): Response
    {
        $data = [];
        /**
         * @var string $supervisorKey
         * @var Supervisor $supervisor
         */
        foreach ($this->supervisorManager->getSupervisors() as $supervisorKey => $supervisor) {
            $data[] = \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor, $supervisorKey);
        }

        return $this->getApiResponse($data, Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Stop process",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess::class))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Response(
     *         response="500",
     *         description="",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=500),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function stopProcessAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if (!$supervisor->stopProcess($this->getProcessIdentification($processGroup, $processName))) {
                return $this->getApiResponse([
                    'message' => $this->translator->trans(
                        'process.stop.error',
                        [],
                        'SupervisorRestBundle'
                    ),
                ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
            }

        } catch (SupervisorException $e) {
            return $this->getApiResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }

        return $this->showProcessInfoAction($supervisorKey, $processName, $processGroup);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Start process",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess::class))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Response(
     *         response="500",
     *         description="token",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=500),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function startProcessAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if (!$supervisor->startProcess($this->getProcessIdentification($processGroup, $processName))) {
                return $this->getApiResponse([
                    'message' => $this->translator->trans(
                        'process.start.error',
                        [],
                        'SupervisorRestBundle'
                    ),
                ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
            }

        } catch (SupervisorException $e) {
            return $this->getApiResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }

        return $this->showProcessInfoAction($supervisorKey, $processName, $processGroup);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Start all processes for supervisor",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::class))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Response(
     *         response="500",
     *         description="",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=500),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function startAllProcessesAction(string $supervisorKey): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }
        try {
            $supervisor->startAllProcesses();
        } catch (SupervisorException $exception) {
            return $this->getApiResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }

        return $this->getApiResponse(
            \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor, $supervisorKey),
            Response::HTTP_OK
        );
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Stop all processes for supervisor",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::class))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Response(
     *         response="500",
     *         description="",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=500),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function stopAllProcessesAction(string $supervisorKey): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            $supervisor->stopAllProcesses();
        } catch (SupervisorException $exception) {
            return $this->getApiResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }

        return $this->getApiResponse(
            \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor, $supervisorKey),
            Response::HTTP_OK
        );
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Log of supervisor",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", type="string")
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function showSupervisorLogAction(string $supervisorKey, int $offset = 0, int $limit = 0): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $logs = $supervisor->readLog($offset, $limit);

        return $this->getApiResponse(['log' => $logs], Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Clear log for supervisor",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200)
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function clearSupervisorLogAction(string $supervisorKey): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if ($supervisor->clearLog() !== true) {
                return $this->getApiResponse([
                    'message' => $this->translator->trans('logs.delete.error', [], 'SupervisorRestBundle'),
                ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
            }
        } catch (SupervisorException $exception) {
            return $this->getApiResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }
        return $this->getApiResponse([], Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Process log",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", type="string")
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process or supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function showProcessLogAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $result = $supervisor->tailProcessStdoutLog($this->getProcessIdentification($processGroup, $processName), 0, 1);
        $stdout = $supervisor->tailProcessStdoutLog($this->getProcessIdentification($processGroup, $processName), 0, (int) $result[1]);


        return $this->getApiResponse(['log' => $stdout[0]], Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Process error log",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", type="string")
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process or supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function showProcessLogErrAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $result = $supervisor->tailProcessStderrLog($this->getProcessIdentification($processGroup, $processName), 0, 1);
        $stderr = $supervisor->tailProcessStderrLog($this->getProcessIdentification($processGroup, $processName), 0, (int) $result[1]);

        return $this->getApiResponse(['log' => $stderr[0]], Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Clear process log",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200)
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process or supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Response(
     *         response="500",
     *         description="",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=500),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function clearProcessLogAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if ($supervisor->clearProcessLogs($this->getProcessIdentification($processGroup, $processName)) !== true) {
                return $this->getApiResponse([
                    'message' => $this->translator->trans('logs.delete.error', [], 'SupervisorRestBundle'),
                ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
            }
        } catch (SupervisorException $exception) {
            return $this->getApiResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR, 'error');
        }
        return $this->getApiResponse([], Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Process info",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess::class))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Process or supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function showProcessInfoAction(string $supervisorKey, string $processName, string $processGroup): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $process = SupervisorProcess::mapProcess($supervisor->getProcess($this->getProcessIdentification($processGroup, $processName)));

        return $this->getApiResponse($process, Response::HTTP_OK);
    }

    /**
     * @SWG\Response(
     *         response="200",
     *         description="Show info about all process for supervisor",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=200),
     *     		   @SWG\Property(property="data", type="array", @SWG\Items(ref=@Model(type=HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess::class)))
     *        )
     * )
     * @SWG\Response(
     *         response="404",
     *         description="Supervisor not found",
     *         @SWG\Schema(
     *             @SWG\Property(property="status", type="string"),
     *             @SWG\Property(property="code", type="integer", example=404),
     *     		   @SWG\Property(property="data", type="array",
     *                @SWG\Items(@SWG\Property(property="message", type="string"))
     *             )
     *        )
     * )
     * @SWG\Tag(name="Supervisor")
     */
    public function showProcessInfoAllAction(string $supervisorKey): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($supervisorKey);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $processes = $supervisor->getAllProcesses();

        $processesInfo = [];
        foreach ($processes as $process) {
            $processesInfo[] = SupervisorProcess::mapProcess($process);
        }
        return $this->getApiResponse($processesInfo, Response::HTTP_OK);
    }

    private function getProcessIdentification(string $processGroup, string $processName): string
    {
        return sprintf('%s:%s', $processGroup, $processName);
    }

    /**
     * @param mixed $data
     * @param int $statusCode
     * @param string $status
     * @return Response
     */
    public function getApiResponse($data, int $statusCode = 200, string $status = 'success'): Response
    {

        $data = [
            'status' => $status,
            'code' => $statusCode,
            'data' => $data,
        ];
        $response = new Response($this->serializer->serialize($data, 'json'), $statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

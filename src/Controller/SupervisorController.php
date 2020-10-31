<?php declare(strict_types=1);

namespace HelpPC\Bundle\SupervisorRestBundle\Controller;

use HelpPC\Bundle\SupervisorRestBundle\DTO\SupervisorProcess;
use HelpPC\Bundle\SupervisorRestBundle\Manager\SupervisorManager;
use JMS\Serializer\SerializerInterface;
use Supervisor\Exception\SupervisorException;
use Supervisor\Supervisor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Swagger\Annotations as SWG;

/**
 * SupervisorController
 */
class SupervisorController extends AbstractController
{
    /** @var string[] */
    private static array $publicInformatics = ['description', 'group', 'name', 'state', 'statename'];

    private SupervisorManager $supervisorManager;
    private TranslatorInterface $translator;
    private SerializerInterface $serializer;

    public function __construct(SupervisorManager $supervisorManager, TranslatorInterface $translator, SerializerInterface $serializer)
    {
        $this->supervisorManager = $supervisorManager;
        $this->translator = $translator;
        $this->serializer = $serializer;
    }

    public function indexAction(): Response
    {
        $data = [];
        /**
         * @var string $supervisorKey
         * @var Supervisor $supervisor
         */
        foreach ($this->supervisorManager->getSupervisors() as $supervisorKey => $supervisor) {
            $data[$supervisorKey] = \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor);
        }

        return $this->getApiResponse($data, Response::HTTP_OK);
    }

    public function stopProcessAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if (!$supervisor->stopProcess($this->getProcessIdentification($group, $name))) {
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

        return $this->showProcessInfoAction($key, $name, $group);
    }

    public function startProcessAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if (!$supervisor->startProcess($this->getProcessIdentification($group, $name))) {
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

        return $this->showProcessInfoAction($key, $name, $group);
    }

    public function startAllProcessesAction(string $key): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

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
            \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor),
            Response::HTTP_OK
        );
    }

    public function stopAllProcessesAction(string $key): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

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
            \HelpPC\Bundle\SupervisorRestBundle\DTO\Supervisor::mapSupervisor($supervisor),
            Response::HTTP_OK
        );
    }

    public function showSupervisorLogAction(string $key, int $offset = 0, int $limit = 0): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $logs = $supervisor->readLog($offset, $limit);

        return $this->getApiResponse(['log' => explode(PHP_EOL, $logs)], Response::HTTP_OK);
    }

    public function clearSupervisorLogAction(string $key): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

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

    public function showProcessLogAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager
            ->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $result = $supervisor->tailProcessStdoutLog($this->getProcessIdentification($group, $name), 0, 1);
        $stdout = $supervisor->tailProcessStdoutLog($this->getProcessIdentification($group, $name), 0, (int) $result[1]);


        return $this->getApiResponse(['log' => explode(PHP_EOL, $stdout[0])], Response::HTTP_OK);
    }

    public function showProcessLogErrAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $result = $supervisor->tailProcessStderrLog($this->getProcessIdentification($group, $name), 0, 1);
        $stderr = $supervisor->tailProcessStderrLog($this->getProcessIdentification($group, $name), 0, (int) $result[1]);

        return $this->getApiResponse(['log' => explode(PHP_EOL, $stderr[0])], Response::HTTP_OK);
    }

    public function clearProcessLogAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        try {
            if ($supervisor->clearProcessLogs($this->getProcessIdentification($group, $name)) !== true) {
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

    public function showProcessInfoAction(string $key, string $name, string $group): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $process = SupervisorProcess::mapProcess($supervisor->getProcess($this->getProcessIdentification($group, $name)));

        return $this->getApiResponse($process, Response::HTTP_OK);
    }

    public function showProcessInfoAllAction(string $key): Response
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            return $this->getApiResponse([
                'message' => $this->translator->trans('supervisor.notFound', [], 'SupervisorRestBundle'),
            ], Response::HTTP_NOT_FOUND, 'error');
        }

        $processes = $supervisor->getAllProcesses();

        $processesInfo = [];
        foreach ($processes as $process) {
            $processesInfo[$process->getName()] = SupervisorProcess::mapProcess($process);
        }
        return $this->getApiResponse($processesInfo, Response::HTTP_OK);
    }

    private function getProcessIdentification(string $group, string $name): string
    {
        return sprintf('%s:%s', $group, $name);
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
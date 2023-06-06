<?php

namespace Plugin\Zipcode\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\Zipcode\Form\Type\Admin\ConfigType;
use Plugin\Zipcode\Repository\ConfigRepository;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/zipcode/config", name="zipcode_admin_config")
     * @Template("@Zipcode/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);
        $data = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $folderName = $data['folder_name'];
            $backupBaseDir = $this->getParameter('plugin_data_realdir').'/Zipcode';
            $backupDir = $backupBaseDir.'/'.date('YmdHis');
            /** @var KernelInterface $kernel */
            $kernel = $this->get('kernel');

            $codeDir = $kernel->getProjectDir();
            $fs = new Filesystem();
            $fs->mkdir($backupDir);
            $dirEnd = $backupDir . '.zip';

            $sourceDir = $codeDir . '/html';
            $zip = new ZipArchive();

            if ($zip->open($dirEnd, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceDir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($sourceDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();
                return (new BinaryFileResponse($dirEnd))->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            }
        }
        return [
            'form' => $form->createView(),
        ];
    }
}

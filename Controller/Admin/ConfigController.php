<?php

namespace Plugin\Zipcode\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\Zipcode\Entity\Config;
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
     */
    public function __construct(){}

    /**
     * @Route("/%eccube_admin_route%/zipcode/config", name="zipcode_admin_config")
     * @Template("@Zipcode/admin/config.twig")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(ConfigType::class);
        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $folderName = $data->getFolderName();
            if(in_array($folderName, Config::FOLDER_NAME)) {
                $backupBaseDir = $this->getParameter('plugin_data_realdir').'/Zipcode';
                $backupDir = $backupBaseDir . '/' . $folderName . date('YmdHis');

                /** @var KernelInterface $kernel */
                $kernel = $this->get('kernel');
                $codeDir = $kernel->getProjectDir();

                $fs = new Filesystem();
                $fs->mkdir($backupDir);

                $dirEnd = $backupDir . '.zip';
                $sourceDir = $codeDir . '/html';
                $zip = new ZipArchive();

                if ($zip->open($dirEnd, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir), RecursiveIteratorIterator::LEAVES_ONLY);
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
        }
        return [
            'form' => $form->createView(),
        ];
    }
}

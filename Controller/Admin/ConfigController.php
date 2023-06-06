<?php

namespace Plugin\Zipcode\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\Zipcode\Entity\ZipCode;
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
            if(in_array($folderName, ZipCode::FOLDER_NAME)) {
                $backupBaseDir = $this->getParameter('plugin_data_realdir').'/Zipcode';
                $backupDir = $backupBaseDir . '/' . $folderName . date('YmdHis');

                /** @var KernelInterface $kernel */
                $kernel = $this->get('kernel');
                $codeDir = $kernel->getProjectDir();
                $sourceDir = $codeDir . '/' . $folderName;

                $fs = new Filesystem();
                $fs->mkdir($backupDir);

                $dirEnd = $backupDir . '.zip';
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
                    $this->clearMessage();
                    return (new BinaryFileResponse($dirEnd))->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
                } else {
                    throw new \Exception('ZIPファイルを開けませんでした。');
                }
            } else {
                $this->addError('郵便番号が失敗しました。 無効なフォルダ名です。', 'admin');
            }
        }
        return [
            'form' => $form->createView(),
        ];
    }
}

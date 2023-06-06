<?php

namespace Plugin\Zipcode\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Plugin\Zipcode\Entity\ZipCode', false)) {
    /**
     * Config
     *
     * @ORM\Table(name="plg_zipcode_config")
     * @ORM\Entity(repositoryClass="Plugin\Zipcode\Repository\ConfigRepository")
     */
    class ZipCode
    {
        const FOLDER_NAME = ['app', 'html', 'src', 'dockerbuild', 'bin'];
        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var string
         *
         * @ORM\Column(name="folder_name", type="string", length=255)
         */
        private $folderName;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return string
         */
        public function getFolderName()
        {
            return $this->folderName;
        }

        /**
         * @param string $folderName
         *
         * @return $this;
         */
        public function setFolderName($folderName)
        {
            $this->folderName = $folderName;

            return $this;
        }
    }
}

<?php

namespace App\Controller;

use App\Repository\SoftwareVersionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api/software/version', name: 'api_software_version', methods: ['POST'])]
    public function getVersion(Request $request, SoftwareVersionRepository $repository): JsonResponse
    {
        $version = $request->request->get('version');
        $hwVersion = $request->request->get('hwVersion');

        if (empty($version)) {
            return new JsonResponse(['msg' => 'Version is required'], 200);
        }

        if (empty($hwVersion)) {
            return new JsonResponse(['msg' => 'HW Version is required'], 200);
        }

        // Ported logic from ConnectedSiteController.php
        $patternST = '/^CPAA_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';
        $patternGD = '/^CPAA_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}(_[A-Z]+)?$/i';
        $patternLCI_CIC = '/^B_C_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
        $patternLCI_NBT = '/^B_N_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';
        $patternLCI_EVO = '/^B_E_G_[0-9]{4}\.[0-9]{2}\.[0-9]{2}$/i';

        $stBool = false;
        $gdBool = false;
        $isLCI = false;
        $lciHwType = '';
        $hwVersionBool = false;

        if (preg_match($patternST, $hwVersion)) {
            $hwVersionBool = true;
            $stBool = true;
        }

        if (preg_match($patternGD, $hwVersion)) {
            $hwVersionBool = true;
            $gdBool = true;
        }

        if (preg_match($patternLCI_CIC, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'CIC';
            $stBool = true;
        } elseif (preg_match($patternLCI_NBT, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'NBT';
            $gdBool = true;
        } elseif (preg_match($patternLCI_EVO, $hwVersion)) {
            $hwVersionBool = true;
            $isLCI = true;
            $lciHwType = 'EVO';
            $gdBool = true;
        }

        if (!$hwVersionBool) {
            return new JsonResponse(['msg' => 'There was a problem identifying your software. Contact us for help.'], 200);
        }

        if (str_starts_with(strtolower($version), 'v')) {
            $version = substr($version, 1);
        }

        $softwareVersions = $repository->findAll();
        $response = null;

        foreach ($softwareVersions as $row) {
            if (strcasecmp($row->getSystemVersionAlt(), $version) === 0) {
                $isLCIEntry = str_starts_with($row->getName(), 'LCI');

                if ($isLCI !== $isLCIEntry) {
                    continue;
                }

                if ($isLCI && stripos($row->getName(), $lciHwType) === false) {
                    continue;
                }

                if ($row->isLatest()) {
                    $response = [
                        'versionExist' => true,
                        'msg' => 'Your system is up to date!',
                        'link' => '',
                        'st' => '',
                        'gd' => ''
                    ];
                } else {
                    $stLink = $stBool ? $row->getSt() : '';
                    $gdLink = $gdBool ? $row->getGd() : '';
                    $latestMsg = $isLCI ? 'v3.4.4' : 'v3.3.7';

                    $response = [
                        'versionExist' => true,
                        'msg' => 'The latest version of software is ' . $latestMsg . ' ',
                        'link' => $row->getLink(),
                        'st' => $stLink,
                        'gd' => $gdLink
                    ];
                }
                break;
            }
        }

        if ($response) {
            return new JsonResponse($response, 200);
        }

        return new JsonResponse([
            'versionExist' => false,
            'msg' => 'There was a problem identifying your software. Contact us for help.',
            'link' => '',
            'st' => '',
            'gd' => ''
        ], 200);
    }
}

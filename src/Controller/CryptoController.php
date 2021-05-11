<?php

namespace App\Controller;

use App\Entity\Crypto;
use App\Form\CryptoType;
use App\Repository\CryptoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("")
 */
class CryptoController extends AbstractController
{
    /**
     * @Route("/", name="crypto_index", methods={"GET"})
     */
    public function index(CryptoRepository $cryptoRepository): Response
    {

        return $this->render('crypto/index.html.twig', [
            'cryptos' => $cryptoRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="crypto_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $crypto = new Crypto();
        $form = $this->createForm(CryptoType::class, $crypto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($crypto);
            $entityManager->flush();

            return $this->redirectToRoute('crypto_index');
        }

        return $this->render('crypto/new.html.twig', [
            'crypto' => $crypto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="crypto_show", methods={"GET"})
     */
    public function show(Crypto $crypto): Response
    {
        return $this->render('crypto/show.html.twig', [
            'crypto' => $crypto,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="crypto_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Crypto $crypto): Response
    {
        $form = $this->createForm(CryptoType::class, $crypto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('crypto_index');
        }

        return $this->render('crypto/edit.html.twig', [
            'crypto' => $crypto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="crypto_delete", methods={"POST"})
     */
    public function delete(Request $request, Crypto $crypto): Response
    {
        if ($this->isCsrfTokenValid('delete' . $crypto->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($crypto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('crypto_index');
    }
    /**
     * @Route("/save_crypto", name="crypto_index", methods={"GET"})
     */
    public function save_crypto(CryptoRepository $cryptoRepository): Response
    {
        //contains symbols commas
        $symbols = [];
        //loop for read symbols of bd
        foreach ($cryptoRepository->findAll() as $symbol) {
            $symbols[] = ($symbol->getsymbol());
        }
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
        $parameters = [
            'convert' => 'USD',
            'symbol' => implode(',', $symbols)
        ];

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $_ENV['COINMARKETCAP']
        ];
        $qs = http_build_query($parameters); // query string encode the parameters
        $request = "{$url}?{$qs}"; // create the request URL


        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers 
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $response = curl_exec($curl); // Send the request, save the response
        $resultats = json_decode($response);
        curl_close($curl); // Close request

        return new JsonResponse('Save ended', 200);
    }
}

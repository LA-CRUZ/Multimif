<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route ("/inscription", name="security_registration")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $encoder)  {
        $manager = $this->getDoctrine()->getManager();
        $user = new User();
        
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() &&  $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $user->addRole('ROLE_USER');

            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Inscription effectuée.');
            return $this->redirectToRoute('security_login');
        }
        

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login (AuthenticationUtils $authUtils){
        $error = $authUtils->getLastAuthenticationError();
        if(!is_null($error))
            $this->addFlash('error', 'Erreur : Identifiants de connexion incorrects.');
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout () {}
}
<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Entity\Reponse;
use App\Entity\Question;
use App\Form\ReponseType;
use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/{_locale}")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, ObjectManager $manager)
    {
        $quiz = new Quiz();
        
        $form = $this->createForm(QuizType::class, $quiz);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($quiz);
            $manager->flush();

            return $this->redirectToRoute('create_question', ['id' => $quiz->getId()]);
        }

        return $this->render('quiz/create.html.twig', [
            'formQuiz' => $form->createView()
        ]);    
    }

    /**
     * @Route("/create_question/{id}", name="create_question")
     */
    public function createQuestion(Request $request, ObjectManager $manager, $id)
    {
        $question = new Question();
        $quiz = $manager->getRepository(Quiz::class)->find($id);

        $reponse1 = new Reponse();
        $question->addReponse($reponse1);
        $reponse2 = new Reponse();
        $question->addReponse($reponse2);
        $reponse3 = new Reponse();
        $question->addReponse($reponse3);
        $reponse4 = new Reponse();
        $question->addReponse($reponse4);
        
        $question->setQuiz($quiz);
        
        $form = $this->createForm(QuestionType::class, $question);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($reponse1);
            $manager->persist($reponse2);
            $manager->persist($reponse3);
            $manager->persist($reponse4);
            $manager->persist($question);
            $manager->flush();

            if($form->get('add')->isClicked())
            {
                return $this->redirectToRoute('create_question', ['id' => $id]);
            } else {
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('quiz/create_question.html.twig', [
            'formQuizQuestion' => $form->createView()
        ]);    
    }
 
    /**
     * @Route("/quiz/{id}", name="quiz")
     */
    public function quiz()
    {
        return $this->render('quiz/quiz.html.twig', [
            'controller_name' => 'MainController',
        ]);    
    }
    
    /**
     * @Route("/resultat", name="resultat")
     */
    public function resultat()
    {
        return $this->render('main/resultat.html.twig', [
            'controller_name' => 'MainController',
        ]);    
    }

    /**
    * @Route("/statistique/{id}", name="create_quiz")
     */
    public function stat()
    {
        return $this->render('quiz/statistique.html.twig', [
            'controller_name' => 'MainController',
        ]);    
    }
}
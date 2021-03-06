<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Reponse;
use App\Entity\Question;
use App\Entity\Result;
use App\Form\QuizType;
use App\Form\ReponseType;
use App\Form\QuestionType;
use App\Form\ResultType;
use App\Repository\QuestionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


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
    public function create(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $quiz = new Quiz();

        $form = $this->createForm(QuizType::class, $quiz);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          if ($form['end']->getData() && $form['deadLine']->getData() != null ) {
            $quiz->setDeadLine(($form['deadLine']->getData()));
          } else {
            $quiz->setDeadLine(null);
          }

        $quiz->setCreator($this->getUser());
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
    public function createQuestion(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager();

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
                $this->addFlash('success', 'Question enregistrée.');
                return $this->redirectToRoute('create_question', ['id' => $id]);
            }
        }

        return $this->render('quiz/create_question.html.twig', [
            'formQuizQuestion' => $form->createView(),
            'quiz' => $quiz,
            'edit' => false
        ]);    
    }

    /**
     * @Route("/remove/{id}", name="remove-quiz")
     */
    public function removeQuiz(Request $request, $id){

        $manager = $this->getDoctrine()->getManager();

        $quiz = $manager->getRepository(Quiz::class)->find($id);

        $manager->remove($quiz);
        $manager->flush();

        $this->addFlash('success', 'Le quiz a bien été supprimé.');

        return $this->redirectToRoute('quiz');
    }

    /**
     * @Route("/edit/{id}", name="edit-quiz")
     */
    public function editQuiz(Request $request, $id){
        $manager = $this->getDoctrine()->getManager();

        $quiz = $manager->getRepository(Quiz::class)->find($id);

        return $this->render('quiz/edit_quiz.html.twig', [
            'controller_name' => 'MainController',
            'quiz' => $quiz
        ]);
    }

    /**
     * @Route("/quiz", name="quiz")
     */
    public function show_quiz_list(){
        $repo = $this->getDoctrine()->getRepository(Quiz::class);

        $quiz_list = $repo->findBycreator($this->getUser());

        return $this->render('quiz/quiz_list.html.twig', [
            'controller_name' => 'MainController',
            'quiz_list' => $quiz_list
        ]);
    }

    public function numhash($n) {
        return (((0x0000FFFF & $n) << 16) + ((0xFFFF0000 & $n) >> 16));
    }

    /**
     * @Route("/quiz/{id}", name="show_quiz")
     */
    public function quiz(Request $request, $id)
    {
        if (strpos($request->headers->get('referer'), 'create_question') !== false)
        {
            $this->addFlash('success', 'Quiz enregistré.');
        }

        $repo = $this->getDoctrine()->getRepository(Quiz::class);

        $quiz = $repo->find($id);

        $idHash = $this->numhash($id);
        return $this->render('quiz/quiz.html.twig', [
            'controller_name' => 'MainController',
            'quiz' => $quiz,
            'codeHash' => $idHash
        ]);
    }

    /**
     * @Route("/statistique/{id}", name="create_quiz")
     */
    public function stat($id)
    {
        $repoQuiz = $this->getDoctrine()->getRepository(Quiz::class);
        $repoRes = $this->getDoctrine()->getRepository(Result::class);
        $quiz = $repoQuiz->find($id);
        $idHash = $this->numhash($id);

        $total_reponse = 0;
        foreach ($quiz->getQuestions() as $question){
            $temp_total = 0;
            $respIds = [];
            foreach($question->getReponses() as $reponse){
                $respIds[] = $reponse->getId(); 
                $result = $repoRes->findByresponse($reponse->getId());
                $temp_total += count($result);
                $stat[$reponse->getId()] = count($result);
            }
            $total_reponse += $temp_total;
            $temp_total = $temp_total == 0 ? 1 : $temp_total;
            
            foreach($respIds as $idReponse)
            {
                $pourcent[$idReponse] = intval(round(($stat[$idReponse] / $temp_total) * 100));
            }
            $totalRepByQuestion[$question->getId()] = $temp_total;
        }

        if( sizeof($quiz->getQuestions()) != 0 && $total_reponse != 0) {
            return $this->render('quiz/statistique.html.twig', [
                'display' => true,
                'controller_name' => 'MainController',
                'stat' => $stat,
                'quiz' => $quiz,
                'total' => $totalRepByQuestion,
                'pourcent' => $pourcent,
                'codeHash' => $idHash
            ]);
        } else {
            return $this->render('quiz/statistique.html.twig', [
                'display' => false,
                'quiz' => $quiz,
                'codeHash' => $idHash
            ]);
        }
    }

    /**
    * @Route("/answer_quiz/{id}", name="answer_quiz")
     */
    public function answer(Request $request, $id)
    {
        $manager = $this->getDoctrine()->getManager();

        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);
        
        $resultArray = [];
        foreach($quiz->getQuestions() as $question) {
            foreach($question->getReponses() as $reponse) {
                $stringId = strval($reponse->getId());

                if($request->request->get($stringId) != null) {
                    $result = new Result();
                    $result->setUser($this->getUser());
                    $result->setResponse($reponse);
                    array_push($resultArray, $result);
                }
            }
        }
        
        foreach($resultArray as $result) {
            $manager->persist($result);
        }
        
        $manager->flush();

        if(sizeof($resultArray) != 0) {
            $this->addFlash('success', 'Votre réponse a été enregistrée.');
            return $this->redirectToRoute('search_quiz');
        } else {
            $form = $this->createForm(ResultType::class);
            $idHash = $this->numhash($id);
            return $this->render('quiz/answer_quiz.html.twig', [
                'formAnswer' => $form->createView(),
                'quiz' => $quiz,
                'codeHash' => $idHash
            ]);
        }
    }


    /**
     * @Route("/remove_question/{id}", name="remove-question")
     */
    public function removeQuestion(Request $request, $id){
        $manager = $this->getDoctrine()->getManager();

        $question = $manager->getRepository(Question::class)->find($id);

        $quiz = $question->getQuiz();

        $manager->remove($question);
        $manager->flush();

        $this->addFlash('success', 'La question a bien été supprimée.');
        return $this->redirectToRoute('edit-quiz', [ 'id' => $quiz->getId()]);
    }

    /**
     * @Route("/edit_question/{id}", name="edit-question")
     */
    public function editQuestion(Request $request, $id){
        $manager = $this->getDoctrine()->getManager();

        $question = $manager->getRepository(Question::class)->find($id);
        $idQuiz = $question->getQuiz()->getId();
        $quiz = $manager->getRepository(Quiz::class)->find($idQuiz);

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            if($form->get('add')->isClicked())
            {
                $this->addFlash('success', 'Question enregistrée.');
                return $this->redirectToRoute('create_question', ['id' => $idQuiz]);
            } else {
                $this->addFlash('success', 'Quiz enregistré.');
                return $this->redirectToRoute('show_quiz', ['id' => $idQuiz]);
            }
        }

        return $this->render('quiz/create_question.html.twig', [
            'formQuizQuestion' => $form->createView(),
            'quiz' => $quiz,
            'edit' => true
        ]);
    }

    /**
     * @Route("/search", name="search_quiz")
     */
    public function search()
    {
        $idHash = isset($_POST['id_quiz']) ? $_POST['id_quiz'] : -1 ;

        if(is_numeric($idHash)) {//Si la valeur entrée est bien un nombre on vérifie que le quiz existe
            if($idHash != -1){//Si id_quiz n'était pas défini, c'est la première arrivée sur la page.
                $id = $this->numhash($idHash);
                $repo = $this->getDoctrine()->getRepository(Quiz::class);
                $quiz = $repo->find($id);
                if(!$quiz){//Si l'id renseigné ne correspond à aucun quiz
                    $this->addFlash('error', 'Erreur : Le quizz indiqué n\'existe pas.');  
                }else{//On a rempli toutes les conditions pour accéder à la page de réponse
                    $depasse = false;
                    
                    if ($quiz->getDeadLine() != null ) {
                        $depasse =  (new \DateTime("now")) > $quiz->getDeadLine();
                    }
                    
                    if($depasse) {
                        $this->addFlash('error', 'Accès impossible ! La date limite du quiz a été dépassé.');
                        return $this->redirectToRoute('search_quiz');
                    }

                    $user = $this->getUser();
                    $idUser = $user->getId();
                    $manager = $this->getDoctrine()->getManager();
                    $query = $manager->createQuery("select distinct  IDENTITY(q.quiz) from App\Entity\Question q inner join App\Entity\Reponse resp where q.id=resp.question 
                    and resp.question in (select IDENTITY(r.question) from App\Entity\Reponse r inner join App\Entity\Result res 
                       where res.response=r.id and res.user=:idUser)");
                    $query->setParameter('idUser', $idUser);  
                    $IdsQuiz = $query->getResult();
                    
                    if (!empty($IdsQuiz)){
                        foreach($IdsQuiz as $idQuiz){
                            if ($idQuiz[1] == $id){
                                $this->addFlash('error', 'Accès impossible ! Vous avez déja répondu à ce quiz');
                                return $this->redirectToRoute('search_quiz');
                            }
                        } 
                    }  
                    return $this->redirectToRoute('answer_quiz', ['id' => $id]);  
                }
            }
        } else $this->addFlash('error', 'Erreur : Merci d\'entrer une valeur numérique.');
        return $this->render('quiz/search_quiz.html.twig', [
            'controller_name' => 'MainController',
        ]);    
    }
}
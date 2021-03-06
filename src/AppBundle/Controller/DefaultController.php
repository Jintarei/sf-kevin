<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\ItemUser;
use AppBundle\Entity\Post;
use AppBundle\Entity\PostCategory;
use AppBundle\Form\PostType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $itemUser;
    private $em;

    public function __construct()
    {
    }

    public function getMenuAction($route)
    {
        $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAll();
       return $this->render('menu.html.twig', ['posts' => $posts, 'route' => $route]);
    }
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $items = $this->getDoctrine()->getRepository('AppBundle:Item')->findAll();
        $lastVotes = $this->getDoctrine()->getRepository('AppBundle:ItemUser')->findBy([], ['id' => 'DESC'], 10);
        shuffle($items);
        $item1 = $items[0];
        $item2 = $items[1];

        $em = $this->getDoctrine()->getManager();
        $itemUser = $em->getRepository(ItemUser::class);
        $userVotes = $itemUser->findBy(['userId' => $this->getUser()], array('id'=>'DESC'), 5);

        return $this->render('default/index.html.twig', [
            'item1' => $item1,
            'item2' => $item2,
            'userVotes' => count($userVotes),
            'userItem' => $userVotes,
            'lastVotes' => $lastVotes,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

//        return $this->getDoctrine()->getRepository('AppBundle:User')->getLastTenVote();
    }
    /**
     * @Route("/search", name="searchhp", defaults={"word" = false})
     * @Route("/search/{word}", name="search")
     */
    public function searchAction(Request $request, $word)
    {
        $news = [];

        if($word){
            $news = $this->getDoctrine()->getRepository('AppBundle:Post')->search( $word );
        }
        $searchForm = $this->createFormBuilder()
            ->add('word', TextType::class, ['label' => 'Recherche'])
            ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
        ->getForm();


        $searchForm->handleRequest($request);
        if($searchForm->isSubmitted() && $searchForm->isValid()){
            $data = $searchForm->getData();
            $news = $this->getDoctrine()->getRepository('AppBundle:Post')->search( $data['word'] );
        }

        // replace this example code with whatever you need
        return $this->render('default/search.html.twig', [
            'news' => $news,
            'form' => $searchForm->createView(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/time/now", name="time_index")
     */
    public function timeAction(){

        return $this->render('default/time.html.twig',
            ['time' => strftime('le %A %d/%m/%Y %H:%M:%S') ]
        );
    }

    /**
     * @Route("/color/{color}", name="color_index", requirements={"color": "[a-zA-Z]+"})
     */
    public function colorAction( $color ){

        return $this->render('default/color.html.twig',
            ['color' => $color ]
        );
    }

    /**
     * @Route("/demo/category", name="demo_category")
     */
    public function demoCategoryAction(){
        // on instantie post catégorie
        $category = new PostCategory();
        // on lui modifie le titre
        $category->setTitle('Category 1');

            // récupération du manager entité
        $em = $this->getDoctrine()->getManager();
        // persist
        $em->persist($category);
        // sauvegarde
        $em->flush();
        return new Response("Sauvegarde OK sur : ".$category->getId() ) ;
    }

    /**
     * @Route("/demo/page1", name="demo_page1")
     */
    public function demoPage1Action(){
        $category = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find( 1 );
        $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find( 1 );
        return new Response("Category : ".$category->getTitle() . ' <br />Post : '.$post->getTitle() ) ;
    }

    /**
     * @Route("/demo/page_listing", name="demo_page_listing")
     */
    public function demoPageListingAction(){

        $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAll();
        return $this->render('default/post_listing.html.twig', ['posts' => $posts]);
    }

    /**
     * @Route("/demo/page/{post}", name="demo_post_show")
     */
    public function demoPostShowAction(Post $post){

        return $this->render('default/post_show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/demo/post", name="demo_post")
     */
    public function demoPostAction(){
        // on instantie post
        $post = new Post();
        $category = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find( 1 );
        // on lui modifie le titre
        $post->setTitle('Post 3');
        $post->setDateCreated( new \DateTime() );
        $post->setEnable(true);
        $post->setContent('Lorem ipsum...');
        $post->setCategory( $category );
        // récupération du manager entité
        $em = $this->getDoctrine()->getManager();
        // persist
        $em->persist($post);
        // sauvegarde
        $em->flush();
        return new Response("Sauvegarde OK sur : ".$post->getId() ) ;
    }

    /**
     * @Route("/demo/delete_category", name="demo_delete_category")
     */
    public function demoCategoryDeleteAction(){
        // on instantie post catégorie
        $category2 = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find(2);
        $category3 = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find(3);
        $category4 = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find(4);
        $category5 = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find(5);
        $category6 = $this->getDoctrine()->getRepository('AppBundle:PostCategory')->find(6);

        // récupération du manager entité
        $em = $this->getDoctrine()->getManager();
        // persist
        $em->remove($category2);
        $em->remove($category3);
        $em->remove($category4);
        $em->remove($category5);
        $em->remove($category6);
        // sauvegarde
        $em->flush();
        return new Response("Sauvegarde OK sur : ".$category->getId() ) ;
    }

    /**
     * @Route("/demo/add/post", name="demo_add_post")
     */
    public function demoAddPost(Request $request)
    {
        $post = new Post();
        /*
                $form = $this->createFormBuilder( $post )
                    ->add('title', TextType::class, ['label' => 'Mon beau titre', 'attr' => ['class' => '']])
                    ->add('dateCreated')
                    ->add('content')
                    ->add('enable')
                    //->add('category')
                    ->add('submit', SubmitType::class)
                    ->getForm()
                ;*/
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();
            $em->persist( $post );
            $em->flush();

            $this->addFlash('success', 'Votre ajout a bien été effectuée.');

            return $this->redirectToRoute('demo_page_listing');

        }
        return $this->render('default/post_add.html.twig', ['formulaire' => $form->createView()]);

    }

    /**
     * @Route("/demo/upd/post/{post}", name="demo_upd_post")
     */
    public function demoUpdPostAction(Request $request,Post $post)
    {

//        $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($post);
/*
        $form = $this->createFormBuilder( $post )
            ->add('title', TextType::class, ['label' => 'Mon beau titre', 'attr' => ['class' => '']])
            ->add('dateCreated')
            ->add('content')
            ->add('enable')
            //->add('category')
            ->add('submit', SubmitType::class)
            ->getForm()
        ;*/

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ){
            $em = $this->getDoctrine()->getManager();
            $em->persist( $post );
            $em->flush();

            $this->addFlash('success', 'Votre ajout a bien été effectuée.');

            return $this->redirectToRoute('demo_page_listing');

        }

        return $this->render('default/post_add.html.twig', ['formulaire' => $form->createView()]);

    }

    /**
     * @Route("/demo/delete/{post}", name="demo_post_delete")
     */
    public function deletePostAction(Post $post){
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Votre suppression a bien été effectuée.');

        return $this->redirectToRoute('demo_page_listing');
    }

     /**
      * @Route("/presentation", name="presentation_index")
      */
    public function presentationAction(Request $request){

          return $this->render('default/presentation.html.twig');

      }

     /**
      * @Route("/news", name="news_index")
      */
    public function newsAction(Request $request){

          return $this->render('default/news.html.twig');

    }

    /**
     * @Route("/tops", name="tops_index")
     */
    public function topsAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:ItemCategory')->findAll();
        $top = [];
        foreach ($categories as $key => $category) {
            $topItem = $em->getRepository('AppBundle:Item')->getTopItemByCategory($category->getId());
            $top[$key]['topItem'] = $topItem;
            $top[$key]['category'] = $category;
        }

        return $this->render('default/tops.html.twig', [
            'top' => $top
        ]);

    }

    /**
     * @Route("/vote/{item}", name="vote_index")
     */
    public function voteAction(Request $request,Item  $item) {

      //  $item = $this->getDoctrine()->getRepository('AppBundle:Item')->find($item); // Possibilité d'utiliser cette méthode à la place de "Item $item" ci-dessus
        $item->setNbVote( $item->getNbVote() +1); // On set un Vote, et on incrémente de +1
        $em = $this->getDoctrine()->getManager(); //$em = entities manager | getDoctrine pour aller chercher la doctrine | getManager pour la base de donnée
        $em->persist($item); // Modification pour l'entité item.
        $em->flush();
        //$url = $this->generateUrl('vote', array('slug' => 'my-blog-post'));
        $this->addFlash('success', 'Votre vote a bien été comptabilisé.');

        $user = $this->getUser();
        if ($user) {
            $user->addItem($item);
            $itemUser = new ItemUser();
            $itemUser->setItemId($item);
            $itemUser->setUserId($user);

            $em->persist($itemUser); // Modification pour l'entité item.
            $em->flush();
        }

        return $this->redirectToRoute('homepage'); // redirectToRoute permet de rediriger vers le name d'une url, ici homepage = index.twig.html

    }

    /**
     * @Route("/", name="homepage")
     */
    /*public function getLastTenVote(Request $request,Item  $item) {

        return $this->getDoctrine()->getRepository('AppBundle:Item')->getLastTenVote();

    }*/

    /*public function categoryListAction(Request $request){
        $categories = $this->getDoctrine()->getRepository('AppBundle:ItemCategory')->findAll();
        //$categories = $this->getDoctrine()->getRepository('AppBundle:ItemCategory')->findOneBySlug($slug);
        if($categories){
            foreach ($categories as $category){
                $category->itemGagnant = $this->getDoctrine()->getRepository('AppBundle:item')->findOneBy(['category'=>$category],['nbVote' => 'desc']);
            }
        }
    } */


}

<?php

namespace App\Controller;

use App\Entity\Books;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("/books", name="book")
     */
    public function index()
    {	
    	
    	$books = $this->getBooks();

        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $books
        ]);
    }

    public function getBooks(){
    	$repository = $this->getDoctrine()->getRepository(Books::class);
    	$books = $repository->findAll();

    	return $books;
    }

    public function getBooksByTitle( string $title = "" ){
    	
    	if( empty($title) ){
    		return $this->getBooks();
    	}

    	$em = $this->getDoctrine()->getRepository(Books::class);
    	$books = $em->createQueryBuilder('b')
	        ->where('lower(b.name) = lower(:name)')
	        ->setParameter(':name', $title)
	        ->getQuery()
	        ->getResult();

    	return $books;
    }

    public function getBookById( int $book_id ){
    	
    	$em = $this->getDoctrine()->getRepository(Books::class);
    	$book = $em->createQueryBuilder('b')
	        ->where('b.id = :book_id')
	        ->setParameter(':book_id', $book_id)
	        ->getQuery()
	        ->getSingleResult();

    	return $book;
    }
}

<?php

namespace App\Controller;

use App\Controller\BookController;
use App\Controller\ReviewController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
	private $_booksManager;
	private $_reviewsManager;

	public function __construct(BookController $booksManager, ReviewController $reviewsManager)
    {
        $this->_booksManager = $booksManager;
        $this->_reviewsManager = $reviewsManager;
    }

    /**
     * @Route("/", name="main")
     */
    public function index()
    {

    	$books1 = $this->findBooksByString("ZieLoNa MiLa|age>30");
    	$books2 = $this->findBooksByString("ZiElonA Droga|age<30");

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'books1' => $books1,
            'books2' => $books2,
        ]);
    }

    /**
     * @Route("/search", name="search_book")
     */
    public function searchBook(Request $request){

    	$search = $request->request->get('search');  
    	
    	$books = $this->findBooksByString( $search );

    	return $this->render('main/parts/table.html.twig', [
            'controller_name' => 'MainController',
            'books' => $books,
        ]);

    }

    public function findBooksByString( string $search ){

    	$age_op = "";
    	$age = 0;

    	if( strpos($search, "|") !== false ){
    	 	$search_parts = explode("|", $search);
    	 	$title = trim( $search_parts[0] );
    		
    		if( preg_match('/age\s*[<>]\s*\d+/', $search_parts[1]) ){
    			$age_expr_row = trim( str_replace( array("age", " "), "", $search_parts[1]) );
    		
	    		$age_op = $age_expr_row[0];
	    		$age = intval( substr($age_expr_row, 1) );
    		}

    	} else {
    	 	$title = $search;
    	}

    	$books = $this->findBooks( $title, $age, $age_op );

    	return $books;

    }

    public function findBooks( string $title = "", int $age = 0, string $age_op = "" ){
    	
    	$books_reviews = $this->findBooksWithTitle( $title, $age, $age_op );

    	if( empty($books_reviews ) ){
    		$books_reviews = $this->findSimilarBooks( $title, $age, $age_op );
    	}

	    return $books_reviews;

    }

    public function findBooksWithTitle( string $title = "", int $age = 0, string $age_op = "" ){
		
		$books_reviews = array();
    	$books = $this->_booksManager->getBooksByTitle( $title );

    	foreach ($books as $key => $book) {
    		$reviews = $this->_reviewsManager->getReviewsAvgAgeForBook( $book->getId(), $age, $age_op );
    		
    		$books_reviews[$book->getId()] = array(
    			"name" => $book->getName(),
    			"compatibility" => $this->calculateCompatibility( $title, $book->getName() ),
    			"book_date" => $book->getBookDate(), 
    			"female_avg" => isset($reviews['f']) ? round($reviews['f'], 2) : 0,
    			"male_avg" => isset($reviews['m']) ? round($reviews['m'], 2) : 0,
    		);
    	}

    	return $books_reviews;
    }

    public function findSimilarBooks( string $title = "", int $age = 0, string $age_op = "" ){

    	$books_reviews = array();
    	$reviews = $this->_reviewsManager->getBooksForReviewsAvgAge( $age, $age_op  );
		
    	foreach ($reviews as $book_id => $review) {
    		$book = $this->_booksManager->getBookById( $book_id );

    		$books_reviews[$book->getId()] = array(
    			"name" => $book->getName(),
    			"compatibility" => $this->calculateCompatibility( $title, $book->getName() ),
    			"book_date" => $book->getBookDate(), 
    			"female_avg" => isset($review['f']) ? round($review['f'], 2) : 0,
    			"male_avg" => isset($review['m']) ? round($review['m'], 2) : 0,
    		);
    	}
		
		usort($books_reviews, function ($a, $b) { return $b['compatibility'] - $a['compatibility']; });

		return $books_reviews;	
    }

    public function calculateCompatibility( string $request, string $book_title ){
    	
    	$request = strtolower($request);
    	$book_title = strtolower($book_title);

    	similar_text( $request, $book_title, $compat_percent1);
		similar_text( $book_title, $request, $compat_percent2);

    	$compat_percent = round( max($compat_percent1, $compat_percent2), 2);

    	return $compat_percent;
    }
}

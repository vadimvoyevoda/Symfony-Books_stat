<?php

namespace App\Controller;

use App\Entity\Reviews;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{

	private $_allowed_operators = array(">", "<");

    /**
     * @Route("/review", name="review")
     */
    public function index()
    {
        return $this->render('review/index.html.twig', [
            'controller_name' => 'ReviewController',
        ]);
    }

    public function getReviews(){
    	$repository = $this->getDoctrine()->getRepository(Reviews::class);
    	$reviews = $repository->findAll();

    	return $reviews;
    }

    private function _get_age_operator( string $age_op = "" ){

    	$age_op = in_array( $age_op, $this->_allowed_operators ) ? $age_op : ">";
    	
    	return $age_op;
    }

    public function getReviewsAvgAgeForBook( int $book_id, int $age = 0, string $age_op = ">"){
    	
    	$reviews = array();

    	if( empty($book_id) ){
    		return $reviews;
    	}

    	$age_op = $this->_get_age_operator($age_op);

    	$em = $this->getDoctrine()->getRepository(Reviews::class);
		$qb = $em->createQueryBuilder('r')
			->select('r.sex, AVG(r.age) avg_age')
			->where('r.book = :book_id AND r.age ' . $age_op . ' :age')
			->groupBy('r.sex')
	   		->setParameters( array(
		        	':book_id' => $book_id, 
		        	':age' => $age
	        	)
	        );

	 	$db_reviews = $qb->getQuery()->getResult();

	    foreach ($db_reviews as $key => $review) {
	    	$reviews[ $review['sex'] ] = $review['avg_age'];
	    }

	   	return $reviews;
    }

    public function getBooksForReviewsAvgAge( int $age = 0, string $age_op = ">"){
    	
    	$reviews = array();

    	$age_op = $this->_get_age_operator($age_op);

    	$em = $this->getDoctrine()->getRepository(Reviews::class);
    	$qb = $em->createQueryBuilder('r')
			->select('IDENTITY(r.book) book_id, r.sex, AVG(r.age) avg_age')
			->where('r.age ' . $age_op . ' :age')
			->groupBy('r.book, r.sex')
	   		->setParameter( ':age', $age );

	 	$db_reviews = $qb->getQuery()->getResult();

	 	foreach ($db_reviews as $key => $review) {
	 		$reviews[ $review['book_id'] ][ $review['sex'] ] = $review['avg_age'];
	    }

	   	return $reviews;
    }
}

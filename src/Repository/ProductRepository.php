<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    public function findAllWithCategoryTagsColors(): array
    {


        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p, c, t, co
            FROM App\Entity\Product p
            LEFT JOIN p.category c
            LEFT JOIN p.tags t
            LEFT JOIN p.colors co
            '
        );

        return $query->getResult();

        /*
        return $this->createQueryBuilder('p')
            ->select('p', 'c', 't', 'co')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.tags', 't')
            ->leftJoin('p.colors', 'co')
            ->getQuery()
            ->getResult();
            */
    }
    public function findWithRelations(array $relations): array
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', ...$relations);
        foreach($relations as $relation){
            $query->leftJoin('p.' . $relation, $relation);
        }
        return $query->getQuery()->getResult();
    }
    /**
 * Product::with('category','tags','colors')->get();
 * 
 * select * from products p
 * left join category c
 * on p.category_id = c.id 
 * left join tags t
 * on p.tag_id = t.id 
 * left join colors co
 * on p.color_id = co.id
 * 
 * 
 * 
 * 
 * 
 * 
 */

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

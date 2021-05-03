<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    /**
     * Encodeur de mot de passe
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }



    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');


        $users = [];
        //USABLE USER
        $user = new User();
        $user
            ->setEmail("mail@mail.com")
            ->setName('Admin')
            ->setPassword($this->encoder->encodePassword($user, 'password'));
        $manager->persist($user);
        $userAdmin = $user;

        $users[] = $user;

        //Fixture USERS
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user
                ->setEmail($faker->email())
                ->setName($faker->name())
                ->setPassword($this->encoder->encodePassword($user, $faker->password()));
            
            $manager->persist($user);

            //dd($user);

            //Adding REFERENCE
            $this->addReference('user_'.$i, $user);

            $users[] = $user;
        }
        //Adding all users in this table
        $users[] = $user;

        //Fixture POSTS
        for ($i = 1; $i <= 10; $i++) {
            $post = new Post();
            $post
                ->setTitle($faker->city())
                ->setIntroduction($faker->catchPhrase())
                ->setContent($faker->text(1000));
            $manager->persist($post);

            //Adding REFERENCE
            $this->addReference('post_'.$i, $post);
        }


        //Fixtures COMMENTS
        for ($h = 1; $h <= 50; $h++) {
            $comment = new Comment();
            $comment
                ->setUser($this->getReference('user_'.$faker->numberBetween(1, 10)))
                ->setContent($faker->text(100))
                ->setPost($this->getReference('post_'.$faker->numberBetween(1, 10)))
                ->setCreatedAt($faker->dateTime());
            $manager->persist($comment);
        }

        //ADMIN COMMENTS
        for ($h = 1; $h <= 10; $h++) {
            $comment = new Comment();
            $comment
                ->setUser($userAdmin)
                ->setContent($faker->text(100))
                ->setPost($this->getReference('post_'.$faker->numberBetween(1, 10)))
                ->setCreatedAt($faker->dateTime());
            $manager->persist($comment);
        }
        $manager->flush();
    }
}

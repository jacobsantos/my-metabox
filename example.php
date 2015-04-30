<?


/**
 * A parent class
 */
class Animal {
    public function makeSound() {
        echo 'Roar!!!';
    }
}

/**
 * Classes that extend \Animal
 */
class Bear extends Animal {}

class Dog extends Animal {
    public function makeSound() {
        echo 'Woof!';
    }   
}

$bear = new Bear;
$bear->makeSound(); // Roar!!!

$dog = new Dog;
$dog->makeSound(); // Woof!
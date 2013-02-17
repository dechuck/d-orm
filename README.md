# D-orm

D-orm is a little PHP ORM who is working with the PDO object. It's an abstract class you extend on your other class.
Your class need to reflect the table it represent.

### Notice
It's the first version of D-orm, there is still a lot to do.  If you have suggestions, don't hesitate to contact me.

## Configure the class  

	require_once 'model.php';
	class Student extends Model
	{
		function __construct()
		{
			$this->link('students');
			$this->belongs_to('teacher', 'teacher_id');
			$this->publics_variables(array('name'));
		}
	}
	
 Then in your constructor, you need to connect the class with his table.
 	
 	$this->link('students');
 	
 If you have link between table, you can reflect it with the function **belongs_to**  or **has_many**
 
 	$this->belongs_to('teachers', 'teacher_id');
 	
 If we would be in the teacher's model, we'll need to connect it with the student, like this.
 
 	$this->has_many('students', 'teacher_id');
 	
The method **belongs_to** return just one object and the methods **has_many** can return multiple objects.

You specify wich values are public with the method **publics_variables**
	
	$this->publics_variables(array('nom'));

If you all your variables to be public except one or two, you can use the method **privates_variables** 

	$this->privates_variables(array('id'));
	
 	

## Interact with the model
### Read methods
You can use many methods to recuperate data from database.
The first one is the method **_find** who return just one object.

	$student = new Student();
	$student->_find(2);

You can also use the **_find** method to find multiple objects.

	$students = new Student();
	$students->_find(2,3,4);
	
You can also use the **_where** method. 

	$students = new Student();
	$students->_where( array("name" => "charles", "teacher_id" => 1) );
	
If you what to limit your search, you can use the method **_limit**.
You'll need to enter the number of element you want and on wich page your are.  If you don't enter params, the method will return you, the ten first elements.

	$students = new Student();
	$students->_limit( 5, 2 );

You can also order the students with the metod **_order**. You just need to enter all the value you'll use for the order and if you use the DESC or ASC order.
	
	$students = new Students();
	$students->_order( array("name"=>"DESC", "teacher_id"=>ASC) );
	
When you use a method who return mutliple objects, you can use method on it.  You can select the first or last object of a where.

	$first_student = new Student();
	$first_student->_where( array("name" => "Bob", "teacher_id" => 1) )->_first();
	
You can do that with those methods
	
	_find();
	_first();
	_last();
	_order();
	_limit();

### The map method
You can use the **_map** method the find an object who is link with an other. In this exemple, the object student is link with the object teacher with the method **belongs_to**.  So you can retrive the teacher object passing by the student.

	$student = new Student();
	$teacher = student->_map('teachers');

## Create methods
	
To create a new object you have to possibilities. You can use the method **_create** to pass directly the variables.

	$new_student = new Student();
	$new_student->_create( array("name"=>"Bob", "teacher_id"=>"1") );
	
Or you can set all the data to the object and use the method **_save**
	
	$new_student = new Student();
	$new_student->name = "Bob";
	$new_student->teacher_id = "2";
	$new_student->_save();
	
You can also use the method **_values** to set many variables in the object, but not in the database.
	
	$new_student = new Student();
	$new_student->_values( array("name"=>"Bob", "teacher_id"="2") );
	$new_student->_save();
	
## Update methods

Like it the last exemple, you can set all the data and use the **_save** methods.

You can also use the **_update** method to update directly the variables in the object **AND** database.

	$updated_student = new Student();
	$updated_student->_update( array("name"=>"Francesco") );
	
## Delete method

To delete an object from the database, you need to use the method **_delete** on a specific object, otherwise it will not work.

	$deleted_student = new Student();
	$deleted_student->_find(1)->_delete();
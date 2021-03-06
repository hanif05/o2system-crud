<?php
/**
 * Created by O2System Framework File Generator.
 * DateTime: 10/10/2019 11:10
 */

// ------------------------------------------------------------------------

namespace App\Controllers;

// ------------------------------------------------------------------------

use O2System\Framework\Http\Controller;
use O2System\Spl\DataStructures\SplArrayObject;

/**
 * Class Students
 * @author hanif
 * @package \App\Controllers
 */
class Students extends Controller
{
    /**
     * Students::index
     *
     * @return void
     */
    public function index()
    {

        view('students/index', [
            'data' => $this->model->all(),
        ]);
    }

    // ------------------------------------------------------------------------

    /**
     *Students::form
     *
     * @return void
     */
    public function form($id = null)
    {
       $vars = [
           'post' => new SplArrayObject(),
       ];

       if($post = $this->input->post()) {
           $vars['post'] = $post;
           if(empty($post->id)) {
               $this->model->insert($post->getArrayCopy());
           }

           redirect_url('/students');
       }
//
       return view('students/form', $vars);

    }

    // ------------------------------------------------------------------------

    /**
     * Students::delete
     *
     * @param  int $id Student id
     * @return void
     */
    public function delete($id)
    {
        $this->model->delete($id);
        redirect_url('/students');
    }

    // ------------------------------------------------------------------------

    /**
     * Students::edit
     *
     * @param  int $id Student id
     * @return void
     */
    public function edit($id)
    {
//        $id = $this->model->id;
        $student = $this->model->find($id);


        return view('students/edit', ['data' => $student]);
    }

    // ------------------------------------------------------------------------

    /**
     * Students::update
     *
     * @param  int $id Student id
     * @return void
     */

    public function update($id)
    {
        $vars = [
            'post' => new SplArrayObject()
        ];
        if ($post = $this->input->post()){
            $student = $this->model->find($id);
            $student->update($post->getArrayCopy());
        }
        redirect_url('/students');



//        $id = $this->input->post('id');
//        $name = $this->input->post('name');
//
//        $data = array(
//          'name' => $name
//        );
//
//        $where = array(
//            'id' => $id
//        );
//
//        $this->model->update($where, $data);
//        redirect_url('/students');
    }
}
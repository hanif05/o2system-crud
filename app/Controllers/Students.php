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
 *
 * @package \App\Controllers
 */
class Students extends Controller
{
    /**
     * Students::index
     */
    public function index()
    {

        view('students/index', [
            'data' => $this->model->all(),
        ]);
    }

    /**
     *
     */
    public function  form($id = null)
    {
       $vars = [
           'post' => new SplArrayObject(),
       ];

       if($post = $this->input->post()) {
           $vars['post'] = $post;
           if(empty($post->id)) {
               $this->model->insert($post->getArrayCopy());
           } else {
               $this->model->update($post->getArrayCopy());
           }

           redirect_url('/students');
       }
//
       return view('students/form', $vars);

    }

    public function delete($id)
    {
        $this->model->delete($id);
        redirect_url('/students');
    }

    public function edit($id)
    {
//        $id = $this->model->id;
        $student = $this->model->find($id);


        return view('students/edit', ['data' => $student]);
    }
}
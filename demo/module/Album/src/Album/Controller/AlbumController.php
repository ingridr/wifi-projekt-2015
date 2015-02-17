<?php

//Controller soll möglichste klein gehalten werden, Validierung soll im Model sein oder in der Entity
namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;
use Album\Form\AlbumForm;


 class AlbumController extends AbstractActionController
 {
     public function indexAction()
     {
       $objectManager = $this
         ->getServiceLocator()
         ->get('Doctrine\ORM\EntityManager');

       $albums = $objectManager->getRepository('Album\Entity\Album')->findAll();
       return new ViewModel(array(
             'albums' => $albums,
       ));
     }
	 
	 
	 
	 

     public function addAction()
     {
         $form = new AlbumForm();
         $form->get('submit')->setValue('Add');
		//im get-Kontext:
         $request = $this->getRequest();
		 //post: also Daten schicken über form:
         if ($request->isPost()) {
			//new Album bezieht sich auf das Model Album:
             $album = new Album();
             $form->setInputFilter($album->getInputFilter());
             $form->setData($request->getPost());

             if ($form->isValid()) {
             	   $objectManager = $this
                    ->getServiceLocator()
                    ->get('Doctrine\ORM\EntityManager');

                 $data = $form->getData();
                 $ae = new \Album\Entity\Album();
                 $ae->setTitle($data['title']);
                 $ae->setArtist($data['artist']);

                $objectManager->persist($ae);
                $objectManager->flush();
                 // Redirect to list of albums
                 return $this->redirect()->toRoute('album');
             }
         }
         return array('form' => $form);

     }

     public function editAction()
     {
	     $objectManager = $this
           ->getServiceLocator()
           ->get('Doctrine\ORM\EntityManager');

         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('album', array(
                 'action' => 'add'
             ));
         }

         // Get the Album with the specified id.  An exception is thrown
         // if it cannot be found, in which case go to the index page.
         try {
             #$album = $this->getAlbumTable()->getAlbum($id);
			 $ae = $objectManager->find('Album\Entity\Album', $id);
         }
         catch (\Exception $ex) {
             return $this->redirect()->toRoute('album', array(
                 'action' => 'index'
             ));
         }

		 $album = new Album($ae);
         $form  = new AlbumForm();
		 #$album->title = $ae->getTitle();
         #$album->artist = $ae->getArtist();
		 //Nächste Zeile sorgt dafür dass die Daten aus dem Model da sind und noch dazu in den richtigen Feldern der Form sind, 
		 //das sieht man in den vorigen auskommentierten 2 Zeilen, das Zuordnen der Datn in die Felder
		 $form->bind($album);
         $form->get('submit')->setAttribute('value', 'Edit');

         $request = $this->getRequest();
         if ($request->isPost()) {
             $form->setInputFilter($album->getInputFilter());
             $form->setData($request->getPost());

             if ($form->isValid()) {
                #$this->getAlbumTable()->saveAlbum($album);
				$data = $form->getData();
				#echo $data->title;
                #$ae->setTitle($data->title);
                #$ae->setArtist($data->artist);
                #exit;
				$objectManager->persist($data->getEntity());
                $objectManager->flush();

                 // Redirect to list of albums
                 return $this->redirect()->toRoute('album');
             }
         }

         return array(
             'id' => $id,
             'form' => $form,
         );
     }

	 public function deleteAction()
     {
         $objectManager = $this
           ->getServiceLocator()
           ->get('Doctrine\ORM\EntityManager');
 
         $id = (int) $this->params()->fromRoute('id', 0);
		 # echo "Id $id";
		 
         if (!$id) {
             return $this->redirect()->toRoute('album');
         }

         #$album = $objectManager->getRepository('Album\Entity\Album')
		 #          ->findOneBy(array('id' => $id));
         $album = $objectManager->find('Album\Entity\Album', $id);  # vereinfachte Suche - geht nur für Suche nach id				   
		 #echo "Id $id". $album->getTitle() ;

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $objectManager->remove($album);     # löschen
				 $objectManager->flush();
             }

             // Redirect to list of albums
             return $this->redirect()->toRoute('album');
         }

         return array(
             'id'    => $id,
             'album' => $album     # in diesem Fall wird einen spezielle Form, nämlich die im View view "delete.phtml" wird aufgerufen (deleteAction --> delete-view wird aufgerufen)
         );
     }
	 
	 public function fuffyAction()
     {
         return array('title'    => 'Fuffy');	   
     }
	 
	  public function birgitAction()
     {
         return array('title'    => 'Birgit!');	   
     }
	 
	 
 }
<?php


namespace PTS\SyliusOrderBatchPlugin\Service;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class FormErrorExtractor
{
    public function getErrorMessages(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            /**
             * @var FormInterface $child
             */
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}

<?php
/**
 * This file is part of the Binary Spanner Quick Forms bundle.
 * Copyright (c) 2017 Chris Bradbury
 *
 * Please view the LICENSE file that accompanied this source code for further copyright and license information.
 */

namespace BinarySpanner\QuickForms\Tests\Service;

use BinarySpanner\QuickForms\Type\QuickFormsType;
use PHPUnit\Framework\TestCase;
use BinarySpanner\QuickForms\Service\QuickForms;

use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\DependencyInjection\Container;

use BinarySpanner\QuickForms\Entity\QuickFormsData;


use Symfony\Component\Form\FormBuilder;

/**
 * Class QuickFormsTest
 * @package BinarySpanner\QuickForms\Tests\Service
 * @coversDefaultClass BinarySpanner\QuickForms\Service\QuickForms
 */
class QuickFormsTest extends TestCase
{
    //TODO This really should be a proper integration test...
    /**
     * Test that ::loadForms returns the correct output.
     *
     * @covers ::__construct
     * @covers ::loadForms
     * @covers ::loadDataClass
     * @covers ::buildFormFields
     *
     * @uses BinarySpanner\QuickForms\Type\QuickFormsType
     * @uses BinarySpanner\QuickForms\Entity\QuickFormsData
     */
    public function testLoadFormsReturnsCorrectlyFormattedOutput()
    {
        $formSetupInfo = [
            'formName' => [
                'theme' => 'formTheme',
                'fields' => [
                    'fieldOne' => [
                        'id' => 'fieldOneId',
                        'type' => 'text'
                    ],
                    'fieldTwo' => [
                        'id' => 'fieldTwoId',
                        'type' => 'text'
                    ],
                ]
            ]
        ];

        $data = new QuickFormsData();
        $formType = new QuickFormsType();
        $prophecyArgument = new Argument();

        $formMock = $this->prophesize(Form::class);
        $formMock->createView()->willReturn('formView');
        $formMock = $formMock->reveal();

        $formBuilderMock = $this->prophesize(FormBuilder::class);
        $formBuilderMock->getForm()->willReturn($formMock);
        $formBuilderMock->add(
            $prophecyArgument->any(),
            $prophecyArgument->any(),
            $prophecyArgument->any()
        )->shouldBeCalled();

        $formBuilderMock->reveal();

        $formFactoryMock = $this->prophesize(FormFactory::class);
        $formFactoryMock
            ->createNamedBuilder(
                $prophecyArgument->is('formName'),
                $prophecyArgument->is(QuickFormsType::class),
                $prophecyArgument->type(QuickFormsData::class)
            )->willReturn($formBuilderMock);

        $containerMock = $this->prophesize(Container::class);

        $quickForms = new QuickForms(
            $formSetupInfo,
            $data,
            $formType,
            $formFactoryMock->reveal(),
            $containerMock->reveal()
        );

        $expected = [
            'formName' => [
                'view' => 'formView',
                'theme' => 'formTheme'
            ]
        ];

        $failMessage = '::loadForms does not return correctly formatted output.';
        $this->assertSame($expected, $quickForms->loadForms(), $failMessage);
    }
}

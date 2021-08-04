<?php

namespace Digitick\Sepa\Tests\Unit;


use Digitick\Sepa\DomBuilder\DomBuilderFactory;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\File;
use PHPUnit\Util\Xml;

class RealLifeXMLTest extends TestCase
{
    public function testSuccessfulXMLSave()
    {
        $groupHeader = new GroupHeader('SEPA File Identifier',
            'Your Company Name',
            true,
            'street',
            '00000',
            'town',
            'CZ');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation(
            2, // Amount
            'FI1350001540000056', //IBAN of creditor
            'Their Corp', //Name of Creditor
            null,
            'street',
            '00000',
            'town',
            'country'
        );

        //$transfer->setCountry('CZ');
        $transfer->setBic('OKOYFIHH'); // Set the BIC explicitly
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setInstructionId(1);

// Create a PaymentInformation the Transfer belongs to
        $payment = new PaymentInformation(
            'Payment Info ID',
            'FR1420041010050500013M02606', // IBAN the money is transferred from
            'PSSTFRPPMON',  // BIC
            'My Corp',// Debitor Name
            'EUR',
            'street',
            '00000',
            'town',
            'CZ'
        );
// It's possible to add multiple Transfers in one Payment
        $payment->addTransfer($transfer);

// It's possible to add multiple payments to one SEPA File
        $sepaFile->addPaymentInformation($payment);

// Attach a dombuilder to the sepaFile to create the XML output
        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, 'pain.001.001.03');

// Or if you want to use the format 'pain.001.001.03' instead
// $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, 'pain.001.001.03');

        $xml = $domBuilder->asXml();

        file_put_contents('tests/test_data/test.xml', $xml);


        $this->assertEquals(file_get_contents('tests/test_data/test.xml'), $xml);
    }
}

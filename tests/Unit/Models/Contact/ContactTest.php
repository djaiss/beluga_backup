<?php

namespace Tests\Unit\Models\Contact;

use Tests\TestCase;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_belongs_to_an_account()
    {
        $contact = factory(Contact::class)->create([]);
        $this->assertTrue($contact->account()->exists());
    }
}

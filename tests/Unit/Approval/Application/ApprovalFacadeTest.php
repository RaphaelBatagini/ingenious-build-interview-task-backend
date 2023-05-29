<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Approval\Application;

use App\Domain\Enums\StatusEnum;
use App\Modules\Approval\Api\Dto\ApprovalDto;
use App\Modules\Approval\Application\Exceptions\ApprovalStatusAlreadyAssignedException;
use App\Modules\Approval\Application\ApprovalFacade;
use App\Modules\Approval\Api\Events\EntityApproved;
use App\Modules\Approval\Api\Events\EntityRejected;
use Illuminate\Contracts\Events\Dispatcher;
use Mockery;
use Ramsey\Uuid\Uuid;
use stdClass;
use Tests\TestCase;

class ApprovalFacadeTest extends TestCase
{
    protected Dispatcher $dispatcher;
    protected ApprovalFacade $approvalFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = Mockery::mock(Dispatcher::class);
        $this->approvalFacade = new ApprovalFacade($this->dispatcher);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testApproveShouldDispatchEntityApprovedEventAndReturnTrue(): void
    {
        $approvalDto = new ApprovalDto(Uuid::uuid4(), StatusEnum::DRAFT, stdClass::class);

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(EntityApproved::class));

        $result = $this->approvalFacade->approve($approvalDto);

        $this->assertTrue($result);
    }

    public function testRejectShouldDispatchEntityRejectedEventAndReturnTrue(): void
    {
        $approvalDto = new ApprovalDto(Uuid::uuid4(), StatusEnum::DRAFT, stdClass::class);

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(EntityRejected::class));

        $result = $this->approvalFacade->reject($approvalDto);

        $this->assertTrue($result);
    }

    public function testValidateShouldThrowApprovalStatusAlreadyAssignedExceptionWhenStatusIsNotDraft(): void
    {
        $approvalDto = new ApprovalDto(Uuid::uuid4(), StatusEnum::APPROVED, stdClass::class);

        $this->expectException(ApprovalStatusAlreadyAssignedException::class);

        $this->approvalFacade->approve($approvalDto);
    }
}

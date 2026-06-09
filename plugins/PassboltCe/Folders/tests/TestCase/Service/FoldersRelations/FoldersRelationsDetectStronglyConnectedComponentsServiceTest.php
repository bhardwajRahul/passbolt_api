<?php
declare(strict_types=1);

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         4.1.0
 */

namespace Passbolt\Folders\Test\TestCase\Service\FoldersRelations;

use App\Test\Factory\UserFactory;
use Passbolt\Folders\Model\Dto\FolderRelationDto;
use Passbolt\Folders\Model\Entity\FoldersRelation;
use Passbolt\Folders\Service\FoldersRelations\FoldersRelationsDetectStronglyConnectedComponentsService;
use Passbolt\Folders\Test\Factory\FolderFactory;
use Passbolt\Folders\Test\Factory\FoldersRelationFactory;
use Passbolt\Folders\Test\Lib\FoldersTestCase;

/**
 * Passbolt\Folders\Service\FoldersRelations\FoldersRelationsDetectStronglyConnectedComponentsService Test Case
 *
 * @covers \Passbolt\Folders\Service\FoldersRelations\FoldersRelationsDetectStronglyConnectedComponentsService
 */
class FoldersRelationsDetectStronglyConnectedComponentsServiceTest extends FoldersTestCase
{
    /**
     * @var FoldersRelationsDetectStronglyConnectedComponentsService
     */
    private FoldersRelationsDetectStronglyConnectedComponentsService $service;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->service = new FoldersRelationsDetectStronglyConnectedComponentsService();
    }

    public function assertScc(array $scc, array $expectedScc): void
    {
        $this->assertCount(count($expectedScc), $scc, 'The found SCC doe not have the same count of element than the expected one.');

        foreach ($scc as $sccFolderRelation) {
            foreach ($expectedScc as $expectedSccFolderRelation) {
                if (
                    $expectedSccFolderRelation->foreignId === $sccFolderRelation->foreign_id
                    && $expectedSccFolderRelation->folderParentId === $sccFolderRelation->folder_parent_id
                ) {
                    continue 2;
                }
                $this->assertFalse(false, "Folder relation expected in the SCC not found (foreign_id: {$expectedSccFolderRelation->foreignId}, folder_parent_id: {$expectedSccFolderRelation->folderParentId})");
            }
        }
    }

    /*
     * SCC found in one user tree.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InOnUserTree_Ada_ABA_Betty_A_Carole_B()
    {
        [$folderA, $folderB, $userA, $userB, $userC] = $this->insertFixture_Ada_ABA_Betty_A_Carole_B(); // phpcs:ignore
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABA_Betty_A_Carole_B()
    {
        // Ada sees A in B
        // Ada sees B in A
        // Betty sees A at the root
        // Carole sees B at the root
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Carole:O)
        //    |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $folderA = FolderFactory::make(['name' => 'A'])->withFoldersRelationsFor([$userB])->persist();
        $folderB = FolderFactory::make(['name' => 'B'])->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userC])->persist();
        FoldersRelationFactory::make()->user($userA)
            ->foreignModelFolder($folderA)->folderParent($folderB)->persist();

        return [$folderA, $folderB, $userA, $userB, $userC];
    }

    /*
     * Multiple SCCs found in two users trees, returning the smallest of all.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InOnUserTreeReturnSmallest_Ada_ABA_ABCA_Betty_A_Carole_B_Dame_C()
    {
        [$folderA, $folderB, $folderC, $userA, $userB, $userC, $userD] = $this->insertFixture_Ada_ABA_ABCA_Betty_A_Carole_B_Dame_C(); // phpcs:ignore
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABA_ABCA_Betty_A_Carole_B_Dame_C()
    {
        // Ada sees A in B
        // Ada sees B in A
        // Ada sees C in B
        // Ada sees A in C
        // Betty sees A at the root
        // Carole sees B at the root
        // Came sees C at the root
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Carole:O)
        //    |- A (Ada:O, Betty:O)
        //    |- C (Ada:O, Carole:O)
        //       |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $userD = UserFactory::make()->persist();
        $folderA = FolderFactory::make(['name' => 'A'])->withFoldersRelationsFor([$userB])->persist();
        $folderB = FolderFactory::make(['name' => 'B'])->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userC])->persist();
        $folderC = FolderFactory::make(['name' => 'C'])->withFoldersRelationsFor([$userA], $folderB)
            ->withFoldersRelationsFor([$userD])->persist();
        FoldersRelationFactory::make()->user($userA)
            ->foreignModelFolder($folderA)->folderParent($folderB)->persist();
        FoldersRelationFactory::make()->user($userA)
            ->foreignModelFolder($folderA)->folderParent($folderC)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB, $userC, $userD];
    }

    /*
     * SCC found in two users trees with all the folders visible by each users.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InTwoUserTreesWithoutNotCommonIntermediaryFolders_Ada_AB_Betty_BA()
    {
        [$folderA, $folderB, $userA, $userB] = $this->insertFixture_Ada_AB_Betty_BA(); // phpcs:ignore
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_AB_Betty_BA()
    {
        // Ada is owner of all folders
        // Betty is owner of all folders
        // Ada sees A at the root
        // Ada sees B in A
        // Betty sees B at the root
        // Betty sees A in B
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Betty:O)
        //    |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA])->persist();
        $folderB = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userB])->persist();
        FoldersRelationFactory::make()->user($userB)
            ->foreignModelFolder($folderA)->folderParent($folderB)->persist();

        return [$folderA, $folderB, $userA, $userB];
    }

    /*
     * SCC found in two users trees which includes an intermediary folder in the first tree not visible by the other
     * user the cycle is formed with.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InTwoUserTreesWithOneNotCommonIntermediaryFolders_Ada_ABC_Betty_CA_Carole_B()
    {
        [$folderA, $folderB, $folderC, $userA, $userB] = $this->insertFixture_Ada_ABC_Betty_CA_Carole_B(); // phpcs:ignore
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderC->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderC->id, $folderB->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABC_Betty_CA_Carole_B()
    {
        // Ada ABC
        // Betty CA
        // Carole B
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Carole:O)
        //    |- C (Ada:O, Betty:O)
        //       |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA])->persist();
        $folderB = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userC])
            ->persist();
        $folderC = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderB)
            ->withFoldersRelationsFor([$userB])->persist();
        FoldersRelationFactory::make()->user($userB)
            ->foreignModelFolder($folderA)->folderParent($folderC)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB];
    }

    /*
     * SCC found in two users trees which includes intermediary folders in each tree not visible by the other users
     * the cycle is formed with.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InTwoUserTreesWithTwoNotCommonIntermediaryFolders_Ada_ABC_Betty_CDA_Carole_B_D()
    {
        [$folderA, $folderB, $folderC, $folderD, $userA, $userB, $userC] = $this->insertFixture_Ada_ABC_Betty_CDA_Carole_B_D(); // phpcs:ignore
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderC->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderC->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderD->id, $folderC->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABC_Betty_CDA_Carole_B_D()
    {
        // Ada ABC
        // Betty CDA
        // Carole BD
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Carole:O)
        //    |- C (Ada:O, Betty:O)
        //       |- D (Betty:O, Carole:O)
        //          |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA])->persist();
        $folderB = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userC])
            ->persist();
        $folderC = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderB)
            ->withFoldersRelationsFor([$userB])->persist();
        $folderD = FolderFactory::make()
            ->withFoldersRelationsFor([$userB], $folderC)
            ->withFoldersRelationsFor([$userC])
            ->persist();
        FoldersRelationFactory::make()->user($userB)
            ->foreignModelFolder($folderA)->folderParent($folderD)->persist();

        return [$folderA, $folderB, $folderC, $folderD, $userA, $userB, $userC];
    }

    /*
     * No SCC found if there are none to be detected.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCNotFound_InMultipleUserTrees_Ada_ABC_Betty_ABC()
    {
        $this->insertFixture_Ada_ABC_Betty_ABC();

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertEmpty($result);
    }

    public function insertFixture_Ada_ABC_Betty_ABC()
    {
        // Ada ABC
        // Betty ABC
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Betty:O)
        //    |- C (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA, $userB])->persist();
        $folderB = FolderFactory::make()->withFoldersRelationsFor([$userA, $userB], $folderA)->persist();
        $folderC = FolderFactory::make()->withFoldersRelationsFor([$userA, $userB], $folderB)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB];
    }

    /*
     * SCC found in one user tree even if it traverses a personal folder.
     * A cycle in a single user's tree silently corrupts that user's view (the cyclic
     * folders disappear from the UI), so it must be detected and repaired even when
     * the cycle's intermediary is a personal folder.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InOneUserTreeWithOnePersonalIntermediaryFolder_Ada_ABCA_Betty_A_Carole_C()
    {
        [$folderA, $folderB, $folderC] = $this->insertFixture_Ada_ABCA_Betty_A_Carole_C();
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderC->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderC->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABCA_Betty_A_Carole_C()
    {
        // Ada ABCA
        // Betty A
        // Carole C
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O)
        //    |- C (Ada:O, Carole:O)
        //       |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userB])->persist();
        $folderB = FolderFactory::make()->withFoldersRelationsFor([$userA], $folderA)->persist();
        $folderC = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderB)
            ->withFoldersRelationsFor([$userC])->persist();
        FoldersRelationFactory::make()->user($userA)
            ->foreignModelFolder($folderA)->folderParent($folderC)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB, $userC];
    }

    /*
     * SCC found across two users' trees when the cycle traverses one user's personal
     * folder. This is the exact shape produced by the cooperating-users exploit:
     * Ada has a chain through her personal folder, Betty completes the loop with a
     * shared edge. Detection must succeed so the repair service can break it.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_InTwoUserTreesWithOnePersonalIntermediaryFolders_Ada_ABC_Betty_CA()
    {
        [$folderA, $folderB, $folderC] = $this->insertFixture_Ada_ABC_Betty_CA();
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderB->id, $folderA->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderC->id, $folderB->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderA->id, $folderC->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_Ada_ABC_Betty_CA()
    {
        // Ada ABC
        // Betty CA
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O)
        //    |- C (Ada:O, Betty:O)
        //       |- A (Ada:O, Betty:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA])->persist();
        $folderB = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderA)
            ->persist();
        $folderC = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderB)
            ->withFoldersRelationsFor([$userB])->persist();
        FoldersRelationFactory::make()->user($userB)
            ->foreignModelFolder($folderA)->folderParent($folderC)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB];
    }

    /*
     * No SCC found if it cannot be detected in one or two users trees but in three users trees.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_NoSCCFound_InThreeUserTrees_Ada_AB_Betty_BC_Carole_CA()
    {
        $this->insertFixture_Ada_AB_Betty_BC_Carole_CA();

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertEmpty($result);
    }

    public function insertFixture_Ada_AB_Betty_BC_Carole_CA()
    {
        // Ada AC
        // Betty BC
        // Carole CA
        // ---
        // A (Ada:O, Betty:O)
        // |- B (Ada:O, Betty:O)
        //    |- C (Betty:O, Carole:O)
        //       |- A (Ada:O, Carole:O)

        $userA = UserFactory::make()->persist();
        $userB = UserFactory::make()->persist();
        $userC = UserFactory::make()->persist();
        $folderA = FolderFactory::make()->withFoldersRelationsFor([$userA])->persist();
        $folderB = FolderFactory::make()
            ->withFoldersRelationsFor([$userA], $folderA)
            ->withFoldersRelationsFor([$userB])
            ->persist();
        $folderC = FolderFactory::make()
            ->withFoldersRelationsFor([$userB], $folderB)
            ->withFoldersRelationsFor([$userC])->persist();
        FoldersRelationFactory::make()->user($userC)
            ->foreignModelFolder($folderA)->folderParent($folderC)->persist();

        return [$folderA, $folderB, $folderC, $userA, $userB, $userC];
    }

    /*
     * Reproduces the exact database state produced by the cooperating-users exploit:
     * Ada and Carol both own shared folders Alpha and Charlie. Bravo is personal to Ada
     * and sits between them in Ada's tree. Carol moves Alpha into Charlie, which silently
     * builds a 3-edge cycle visible only in Ada's tree (Charlie -> Alpha -> Bravo -> Charlie).
     * Detection must succeed so the repair service can break one edge and restore Ada's tree.
     */

    public function testFoldersRelationsDetectStronglyConnectedComponentsService_SCCFound_ExploitFinalState_Ada_CharlieAlphaBravoCharlie_Carol_CharlieAlpha()
    {
        [$folderAlpha, $folderBravo, $folderCharlie] = $this->insertFixture_ExploitFinalState();
        $expectedScc = [
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderBravo->id, $folderAlpha->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderCharlie->id, $folderBravo->id),
            new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $folderAlpha->id, $folderCharlie->id),
        ];

        $result = $this->service->detectFirstInSharedFolders();
        $this->assertScc($result, $expectedScc);
    }

    public function insertFixture_ExploitFinalState()
    {
        // Ada (victim): Charlie -> Alpha -> Bravo -> Charlie (3-edge cycle, only Ada sees it)
        // Carol (attacker): Charlie at root, Alpha under Charlie
        // ---
        // Alpha   (Ada:O, Carol:O) - parent: Charlie
        // Bravo   (Ada:O)          - parent: Alpha (personal intermediary)
        // Charlie (Ada:O, Carol:O) - parent: Bravo for Ada, NULL (root) for Carol

        $userAda = UserFactory::make()->persist();
        $userCarol = UserFactory::make()->persist();

        // Charlie at root for Carol; will be re-parented to Bravo for Ada below.
        $folderCharlie = FolderFactory::make()->withFoldersRelationsFor([$userCarol])->persist();
        // Alpha under Charlie for both Ada and Carol.
        $folderAlpha = FolderFactory::make()
            ->withFoldersRelationsFor([$userAda, $userCarol], $folderCharlie)
            ->persist();
        // Bravo personal to Ada, parent = Alpha.
        $folderBravo = FolderFactory::make()
            ->withFoldersRelationsFor([$userAda], $folderAlpha)
            ->persist();
        // Ada's Charlie has parent = Bravo (closes the cycle in Ada's tree only).
        FoldersRelationFactory::make()->user($userAda)
            ->foreignModelFolder($folderCharlie)->folderParent($folderBravo)->persist();

        return [$folderAlpha, $folderBravo, $folderCharlie, $userAda, $userCarol];
    }
}

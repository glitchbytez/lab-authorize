<?php
interface ApiContract
{
    // Records — pass a lab name to scope results to that lab, null returns all
    public function getPendingRecords(?string $labFilter = null): array;
    public function getCompletedRecords(?string $labFilter = null): array;
    public function verifyRecord(string $accessionId, string $notes, string $scientist): array;
    public function rejectRecord(string $accessionId, string $notes, string $scientist): array;
    public function recheckRecord(string $accessionId, string $notes): array;

    // Labs
    public function getLabs(): array;
    public function createLab(string $name, ?string $ahfoz): array;
    public function deleteLab(string $name): array;

    // Users
    public function getUsers(): array;
    public function createUser(string $name, string $role, ?string $lab, string $password): array;
    public function deleteUser(string $userId): array;
}
